package lytecache

import (
	"encoding/json"
	"fmt"
	"math"
	"reflect"
	"strconv"
	"strings"
)

// encodeValue picks a wire type code for value and returns its encoded
// bytes. See SPEC.md for the full encoding table.
func encodeValue(value any) ([]byte, int, error) {
	switch v := value.(type) {
	case []byte:
		return v, typeBytes, nil
	case string:
		return []byte(v), typeString, nil
	case int:
		return encodeIntBytes(int64(v)), typeInt, nil
	case int8:
		return encodeIntBytes(int64(v)), typeInt, nil
	case int16:
		return encodeIntBytes(int64(v)), typeInt, nil
	case int32:
		return encodeIntBytes(int64(v)), typeInt, nil
	case int64:
		return encodeIntBytes(v), typeInt, nil
	case uint:
		return encodeUintBytes(uint64(v))
	case uint8:
		return encodeIntBytes(int64(v)), typeInt, nil
	case uint16:
		return encodeIntBytes(int64(v)), typeInt, nil
	case uint32:
		return encodeIntBytes(int64(v)), typeInt, nil
	case uint64:
		return encodeUintBytes(v)
	case float32:
		return encodeFloatBytes(float64(v))
	case float64:
		return encodeFloatBytes(v)
	default:
		// bool, maps, slices, structs, nil, time.Time, etc. -- anything
		// encoding/json can marshal becomes type 4 (JSON). time.Time uses its
		// own MarshalJSON, which produces an RFC 3339 string, matching the
		// cross-language convention.
		buf, err := json.Marshal(value)
		if err != nil {
			return nil, 0, fmt.Errorf("%w: %v", ErrSerialization, err)
		}
		return buf, typeJSON, nil
	}
}

// encodeIntBytes stores an integer as UTF-8 decimal text, not binary --
// this is what lets Incr/Decr be a single atomic SQL UPSERT (see
// atomicIncr): CAST(value AS TEXT) is arithmetic-ready decimal digits.
func encodeIntBytes(v int64) []byte {
	return []byte(strconv.FormatInt(v, 10))
}

func encodeUintBytes(v uint64) ([]byte, int, error) {
	if v > math.MaxInt64 {
		return nil, 0, fmt.Errorf("%w: uint64 value %d exceeds int64 range", ErrSerialization, v)
	}
	return encodeIntBytes(int64(v)), typeInt, nil
}

func encodeFloatBytes(v float64) ([]byte, int, error) {
	if math.IsNaN(v) || math.IsInf(v, 0) {
		return nil, 0, fmt.Errorf("%w: cannot store NaN or Inf", ErrSerialization)
	}
	return []byte(strconv.FormatFloat(v, 'g', -1, 64)), typeFloat, nil
}

// decodeFloatText parses stored float text, additionally accepting the
// NaN/Infinity spellings written by the Python (nan/inf) and Java
// (NaN/Infinity) implementations case-insensitively -- this library never
// writes those spellings itself (see encodeFloatBytes), but still needs to
// read a value written by one of the other implementations.
func decodeFloatText(s string) (float64, error) {
	switch strings.ToLower(s) {
	case "nan":
		return math.NaN(), nil
	case "inf", "infinity":
		return math.Inf(1), nil
	case "-inf", "-infinity":
		return math.Inf(-1), nil
	}
	v, err := strconv.ParseFloat(s, 64)
	if err != nil {
		return 0, fmt.Errorf("%w: stored float value is not valid: %s", ErrSerialization, s)
	}
	return v, nil
}

// decodeInto decodes data (tagged with typeCode) into dest, which must be a
// non-nil pointer. See [Cache.Get] for the supported destination shapes.
func decodeInto(data []byte, typeCode int, dest any) error {
	switch typeCode {
	case typeBytes:
		return assignBytes(dest, data)
	case typeString:
		return assignString(dest, string(data))
	case typeInt:
		n, err := strconv.ParseInt(string(data), 10, 64)
		if err != nil {
			return fmt.Errorf("%w: stored int value is not valid: %s", ErrSerialization, data)
		}
		return assignInt64(dest, n)
	case typeFloat:
		f, err := decodeFloatText(string(data))
		if err != nil {
			return err
		}
		return assignFloat64(dest, f)
	case typeJSON:
		if err := json.Unmarshal(data, dest); err != nil {
			return fmt.Errorf("%w: %v", ErrSerialization, err)
		}
		return nil
	case 5:
		return fmt.Errorf("%w: value_type=5 is a Python-pickle-only format and cannot be read from Go", ErrSerialization)
	case 6:
		return fmt.Errorf("%w: value_type=6 is a Java-serialized-only format and cannot be read from Go", ErrSerialization)
	default:
		return fmt.Errorf("%w: unknown value_type=%d", ErrSerialization, typeCode)
	}
}

func assignBytes(dest any, data []byte) error {
	switch d := dest.(type) {
	case *[]byte:
		*d = append([]byte(nil), data...)
		return nil
	case *string:
		*d = string(data)
		return nil
	case *any:
		*d = append([]byte(nil), data...)
		return nil
	default:
		return fmt.Errorf("%w: cannot decode bytes into %T", ErrSerialization, dest)
	}
}

func assignString(dest any, s string) error {
	switch d := dest.(type) {
	case *string:
		*d = s
		return nil
	case *[]byte:
		*d = []byte(s)
		return nil
	case *any:
		*d = s
		return nil
	default:
		return fmt.Errorf("%w: cannot decode string into %T", ErrSerialization, dest)
	}
}

// assignInt64 uses reflection so any integer or floating-point pointer type
// (*int, *int32, *uint64, *float64, ...) works as a destination, per the
// "sensible conversions" requirement for code 2.
func assignInt64(dest any, n int64) error {
	rv := reflect.ValueOf(dest)
	if rv.Kind() != reflect.Pointer || rv.IsNil() {
		return fmt.Errorf("%w: destination must be a non-nil pointer, got %T", ErrSerialization, dest)
	}
	elem := rv.Elem()
	switch elem.Kind() {
	case reflect.Int, reflect.Int8, reflect.Int16, reflect.Int32, reflect.Int64:
		if elem.OverflowInt(n) {
			return fmt.Errorf("%w: stored int %d overflows %s", ErrSerialization, n, elem.Type())
		}
		elem.SetInt(n)
	case reflect.Uint, reflect.Uint8, reflect.Uint16, reflect.Uint32, reflect.Uint64:
		if n < 0 || elem.OverflowUint(uint64(n)) {
			return fmt.Errorf("%w: stored int %d does not fit in %s", ErrSerialization, n, elem.Type())
		}
		elem.SetUint(uint64(n))
	case reflect.Float32, reflect.Float64:
		elem.SetFloat(float64(n))
	case reflect.Interface:
		elem.Set(reflect.ValueOf(n))
	default:
		return fmt.Errorf("%w: cannot decode int into %s", ErrSerialization, rv.Type())
	}
	return nil
}

func assignFloat64(dest any, f float64) error {
	rv := reflect.ValueOf(dest)
	if rv.Kind() != reflect.Pointer || rv.IsNil() {
		return fmt.Errorf("%w: destination must be a non-nil pointer, got %T", ErrSerialization, dest)
	}
	elem := rv.Elem()
	switch elem.Kind() {
	case reflect.Float32, reflect.Float64:
		elem.SetFloat(f)
	case reflect.Interface:
		elem.Set(reflect.ValueOf(f))
	default:
		return fmt.Errorf("%w: cannot decode float into %s", ErrSerialization, rv.Type())
	}
	return nil
}
