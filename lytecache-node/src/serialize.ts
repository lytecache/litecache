/**
 * Value <-> (bytes, value_type) conversion. See SPEC.md for the full type-code table.
 *
 * Codes 0-4 are cross-language portable: Buffer/string/int/float are stored as native UTF-8
 * text (see the note on INT64/FLOAT64 below), and everything else JSON-encodes. Code 5
 * (Python pickle) and code 6 (Java native serialization) are other languages' escape hatches;
 * this library never writes them, and reading either raises {@link SerializationError} instead
 * of returning raw bytes silently.
 */

import { SerializationError } from "./errors.js";
import { TypeCode, type TypeCodeValue } from "./schema.js";

export interface SerializedValue {
  bytes: Buffer;
  typeCode: TypeCodeValue;
}

export interface DeserializeOptions {
  /** Passed straight through to `JSON.parse` for type-code-4 (JSON) values. */
  reviver?: (key: string, value: unknown) => unknown;
  /**
   * Rehydrates a type-code-4 (JSON) object as an instance of the given class:
   * `Object.assign(Object.create(into.prototype), data)`. The stored data must be a plain
   * object (not an array or primitive).
   */
  into?: new (...args: never[]) => object;
}

const MAX_SAFE = BigInt(Number.MAX_SAFE_INTEGER);
const MIN_SAFE = BigInt(Number.MIN_SAFE_INTEGER);
// int64 bounds, so a stored integer always round-trips through Python's/Java's 64-bit integers.
const INT64_MAX = 9223372036854775807n;
const INT64_MIN = -9223372036854775808n;

function jsonReplacer(_key: string, value: unknown): unknown {
  if (value instanceof Map) {
    throw new SerializationError(
      "cannot serialize a Map -- convert it to a plain object first, e.g. Object.fromEntries(map)",
    );
  }
  if (value instanceof Set) {
    throw new SerializationError(
      "cannot serialize a Set -- convert it to an array first, e.g. Array.from(set)",
    );
  }
  return value;
}

function encodeJson(value: unknown): Buffer {
  let text: string;
  try {
    // undefined here would only happen for values JSON.stringify legitimately drops
    // (nested functions/symbols); a bare `undefined` at the top level is rejected earlier.
    const result = JSON.stringify(value, jsonReplacer);
    if (result === undefined) {
      throw new SerializationError(`cannot serialize a value of type ${typeof value} to JSON`);
    }
    text = result;
  } catch (err) {
    if (err instanceof SerializationError) throw err;
    throw new SerializationError(
      `cannot serialize value to JSON: ${err instanceof Error ? err.message : String(err)}`,
      { cause: err },
    );
  }
  return Buffer.from(text, "utf8");
}

/** Encodes a value to `(bytes, value_type)`. Throws {@link SerializationError} if it can't be. */
export function serialize(value: unknown): SerializedValue {
  if (value === undefined) {
    throw new SerializationError("cannot store undefined -- did you mean null?");
  }
  if (typeof value === "function" || typeof value === "symbol") {
    throw new SerializationError(`cannot serialize a value of type ${typeof value}`);
  }
  if (Buffer.isBuffer(value)) {
    return { bytes: value, typeCode: TypeCode.BYTES };
  }
  if (value instanceof Uint8Array) {
    return { bytes: Buffer.from(value), typeCode: TypeCode.BYTES };
  }
  if (typeof value === "string") {
    return { bytes: Buffer.from(value, "utf8"), typeCode: TypeCode.STR };
  }
  if (typeof value === "bigint") {
    if (value > INT64_MAX || value < INT64_MIN) {
      throw new SerializationError(
        `bigint ${value} is outside the signed 64-bit integer range that can be stored`,
      );
    }
    return { bytes: Buffer.from(value.toString(), "utf8"), typeCode: TypeCode.INT };
  }
  if (typeof value === "number") {
    if (Number.isNaN(value) || !Number.isFinite(value)) {
      throw new SerializationError("cannot store NaN or Infinity");
    }
    if (Number.isInteger(value)) {
      if (value > Number.MAX_SAFE_INTEGER || value < Number.MIN_SAFE_INTEGER) {
        throw new SerializationError(
          `${value} is beyond Number.MAX_SAFE_INTEGER and may have already lost precision; ` +
            "pass a BigInt instead to store it exactly",
        );
      }
      return { bytes: Buffer.from(value.toString(), "utf8"), typeCode: TypeCode.INT };
    }
    return { bytes: Buffer.from(value.toString(), "utf8"), typeCode: TypeCode.FLOAT };
  }
  // Everything else -- plain objects, arrays, booleans, null, Date, class instances (via
  // toJSON() or their enumerable own properties, exactly like JSON.stringify always behaves).
  return { bytes: encodeJson(value), typeCode: TypeCode.JSON };
}

function decodeInt(text: string): number | bigint {
  const big = BigInt(text);
  if (big > MAX_SAFE || big < MIN_SAFE) {
    return big;
  }
  return Number(big);
}

// This library never writes NaN/Infinity (rejected at serialize() time), but Python's
// repr(float('nan'))/repr(float('inf')) is "nan"/"inf" and Java's Double.toString() is
// "NaN"/"Infinity" -- accept all of those spellings on read so a value written by either
// language (however it got there) is still readable rather than silently misparsed.
function decodeFloat(text: string): number {
  const lower = text.toLowerCase();
  if (lower === "nan") return NaN;
  if (lower === "inf" || lower === "infinity") return Infinity;
  if (lower === "-inf" || lower === "-infinity") return -Infinity;
  const value = Number(text);
  if (Number.isNaN(value)) {
    throw new SerializationError(`stored float value is not valid: ${text}`);
  }
  return value;
}

/** Decodes `(bytes, value_type)` back to a value. Throws {@link SerializationError} if it can't be. */
export function deserialize(
  bytes: Buffer,
  typeCode: number,
  options?: DeserializeOptions,
): unknown {
  switch (typeCode) {
    case TypeCode.BYTES:
      return Buffer.from(bytes);
    case TypeCode.STR:
      return bytes.toString("utf8");
    case TypeCode.INT: {
      const text = bytes.toString("utf8");
      try {
        return decodeInt(text);
      } catch (err) {
        throw new SerializationError(`stored integer value is not valid: ${text}`, { cause: err });
      }
    }
    case TypeCode.FLOAT: {
      const text = bytes.toString("utf8");
      try {
        return decodeFloat(text);
      } catch (err) {
        if (err instanceof SerializationError) throw err;
        throw new SerializationError(`stored float value is not valid: ${text}`, { cause: err });
      }
    }
    case TypeCode.JSON: {
      const text = bytes.toString("utf8");
      const data = JSON.parse(text, options?.reviver);
      if (options?.into) {
        if (data === null || typeof data !== "object" || Array.isArray(data)) {
          throw new SerializationError(
            `cannot reconstruct ${options.into.name} from a stored ${Array.isArray(data) ? "array" : typeof data}; ` +
              "'into' rehydration requires the stored value to be a JSON object",
          );
        }
        return Object.assign(Object.create(options.into.prototype) as object, data);
      }
      return data;
    }
    case TypeCode.PYTHON_PICKLE:
      throw new SerializationError(
        "value_type=5 is a Python-pickle-only format and cannot be read from Node.js",
      );
    case TypeCode.JAVA_SERIALIZED:
      throw new SerializationError(
        "value_type=6 is a Java-native serialization format and cannot be read from Node.js",
      );
    default:
      throw new SerializationError(`unknown value_type ${typeCode} in stored row`);
  }
}
