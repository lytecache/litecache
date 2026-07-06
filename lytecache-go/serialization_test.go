package lytecache_test

import (
	"database/sql"
	"errors"
	"math"
	"testing"
	"time"

	lytecache "github.com/YOURUSERNAME/lytecache-go"
)

// rawStoredValue peeks at the literal stored bytes for key via a second,
// raw connection to the same file -- GetBytes is not a "give me the raw
// bytes regardless of type" escape hatch: it decodes per the stored type
// code like any other Get, and encoding/json's *[]byte destination has
// base64-string semantics, which isn't what a wire-format check wants.
func rawStoredValue(t *testing.T, path, key string) []byte {
	t.Helper()
	raw, err := sql.Open("sqlite", path)
	if err != nil {
		t.Fatal(err)
	}
	defer func() { _ = raw.Close() }()
	var value []byte
	err = raw.QueryRow(`SELECT value FROM cache WHERE namespace = 'default' AND key = ?`, key).Scan(&value)
	if err != nil {
		t.Fatal(err)
	}
	return value
}

func TestSerializationBytesRoundTrip(t *testing.T) {
	c := newTestCache(t)
	want := []byte{0, 1, 2, 255, 128}
	if err := c.Set("k", want); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetBytes("k")
	if err != nil || !found || string(got) != string(want) {
		t.Fatalf("got=%v found=%v err=%v", got, found, err)
	}
}

func TestSerializationStringRoundTrip(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", "hello, 世界"); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetString("k")
	if err != nil || !found || got != "hello, 世界" {
		t.Fatalf("got=%q found=%v err=%v", got, found, err)
	}
}

func TestSerializationIntRoundTrip(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", int64(-12345)); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetInt64("k")
	if err != nil || !found || got != -12345 {
		t.Fatalf("got=%d found=%v err=%v", got, found, err)
	}
}

func TestSerializationFloatRoundTrip(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", 2.71828); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetFloat64("k")
	if err != nil || !found || got != 2.71828 {
		t.Fatalf("got=%v found=%v err=%v", got, found, err)
	}
}

func TestSerializationBoolRoundTrip(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", true); err != nil {
		t.Fatal(err)
	}
	var v bool
	found, err := c.Get("k", &v)
	if err != nil || !found || v != true {
		t.Fatalf("v=%v found=%v err=%v", v, found, err)
	}
}

type person struct {
	Name string `json:"name"`
	Age  int    `json:"age"`
}

func TestSerializationStructWithJSONTags(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", person{Name: "Ada", Age: 30}); err != nil {
		t.Fatal(err)
	}
	var p person
	found, err := c.Get("k", &p)
	if err != nil || !found || p != (person{Name: "Ada", Age: 30}) {
		t.Fatalf("p=%+v found=%v err=%v", p, found, err)
	}
}

func TestSerializationSliceRoundTrip(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", []int{1, 2, 3}); err != nil {
		t.Fatal(err)
	}
	var v []int
	found, err := c.Get("k", &v)
	if err != nil || !found || len(v) != 3 || v[0] != 1 || v[2] != 3 {
		t.Fatalf("v=%v found=%v err=%v", v, found, err)
	}
}

func TestSerializationTimeRFC3339(t *testing.T) {
	c := newTestCache(t)
	want := time.Date(2024, 3, 15, 12, 30, 0, 0, time.UTC)
	if err := c.Set("k", want); err != nil {
		t.Fatal(err)
	}
	var got time.Time
	found, err := c.Get("k", &got)
	if err != nil || !found || !got.Equal(want) {
		t.Fatalf("got=%v found=%v err=%v", got, found, err)
	}

	// Confirm it's actually stored as an RFC 3339 JSON string, matching the
	// cross-language convention, not some Go-specific binary encoding.
	raw := rawStoredValue(t, c.Path(), "k")
	if _, err := time.Parse(`"`+time.RFC3339+`"`, string(raw)); err != nil {
		t.Fatalf("stored value %q is not RFC 3339 JSON: %v", raw, err)
	}
}

func TestSerializationNilStoresJSONNull(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", nil); err != nil {
		t.Fatal(err)
	}
	raw := rawStoredValue(t, c.Path(), "k")
	if string(raw) != "null" {
		t.Fatalf("raw=%q, want %q", raw, "null")
	}
}

func TestSerializationUint64OverflowErrors(t *testing.T) {
	c := newTestCache(t)
	var huge uint64 = math.MaxInt64 + 1
	err := c.Set("k", huge)
	if !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization for a uint64 beyond int64 range, got %v", err)
	}
}

func TestSerializationUint64WithinRangeSucceeds(t *testing.T) {
	c := newTestCache(t)
	var v uint64 = math.MaxInt64
	if err := c.Set("k", v); err != nil {
		t.Fatal(err)
	}
	got, found, err := c.GetInt64("k")
	if err != nil || !found || got != math.MaxInt64 {
		t.Fatalf("got=%d found=%v err=%v", got, found, err)
	}
}

type unmarshalableStruct struct {
	Ch chan int
}

func TestSerializationMarshalFailureErrors(t *testing.T) {
	c := newTestCache(t)
	err := c.Set("k", unmarshalableStruct{Ch: make(chan int)})
	if !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization for an unmarshalable value, got %v", err)
	}
}

func TestSerializationNaNAndInfRejectedAtWrite(t *testing.T) {
	c := newTestCache(t)
	if err := c.Set("k", math.NaN()); !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization for NaN, got %v", err)
	}
	if err := c.Set("k", math.Inf(1)); !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization for +Inf, got %v", err)
	}
	if err := c.Set("k", math.Inf(-1)); !errors.Is(err, lytecache.ErrSerialization) {
		t.Fatalf("expected ErrSerialization for -Inf, got %v", err)
	}
}
