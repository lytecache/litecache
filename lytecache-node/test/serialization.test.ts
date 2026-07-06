import type { Database } from "better-sqlite3";
import { afterEach, beforeEach, describe, expect, it } from "vitest";
import { LyteCache, SerializationError } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

describe("serialization round-trips", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("bytes (Buffer)", () => {
    const buf = Buffer.from("hello", "utf8");
    cache.set("k", buf);
    expect(cache.get("k")).toEqual(buf);
  });

  it("bytes (Uint8Array) round-trips as a Buffer", () => {
    const arr = new Uint8Array([1, 2, 3]);
    cache.set("k", arr);
    const result = cache.get<Buffer>("k");
    expect(Buffer.isBuffer(result)).toBe(true);
    expect([...result!]).toEqual([1, 2, 3]);
  });

  it("string", () => {
    cache.set("k", "hello world");
    expect(cache.get("k")).toBe("hello world");
  });

  it("negative integer", () => {
    cache.set("k", -42);
    expect(cache.get("k")).toBe(-42);
  });

  it("negative float", () => {
    cache.set("k", -3.5);
    expect(cache.get("k")).toBe(-3.5);
  });

  it("nested object and array (JSON)", () => {
    const value = { a: [1, 2, { b: "c" }], d: null, e: true };
    cache.set("k", value);
    expect(cache.get("k")).toEqual(value);
  });

  it("Date becomes an ISO-8601 string inside JSON", () => {
    const date = new Date("2024-01-15T10:30:00.000Z");
    cache.set("k", { when: date });
    const result = cache.get<{ when: string }>("k");
    expect(result?.when).toBe("2024-01-15T10:30:00.000Z");
  });

  it("a bare Date at the top level also becomes an ISO string", () => {
    const date = new Date("2024-01-15T10:30:00.000Z");
    cache.set("k", date);
    expect(cache.get("k")).toBe("2024-01-15T10:30:00.000Z");
  });

  it("undefined values inside an object are dropped, matching JSON.stringify", () => {
    cache.set("k", { a: 1, b: undefined });
    expect(cache.get("k")).toEqual({ a: 1 });
  });

  it("a class instance serializes via its enumerable own properties", () => {
    class Point {
      constructor(
        public x: number,
        public y: number,
      ) {}
    }
    cache.set("k", new Point(1, 2));
    expect(cache.get("k")).toEqual({ x: 1, y: 2 });
  });
});

describe("serialization errors", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("rejects a top-level undefined", () => {
    expect(() => cache.set("k", undefined)).toThrow(SerializationError);
  });

  it("rejects a function", () => {
    expect(() => cache.set("k", () => {})).toThrow(SerializationError);
  });

  it("rejects a symbol", () => {
    expect(() => cache.set("k", Symbol("x"))).toThrow(SerializationError);
  });

  it("rejects a Map with a clear message, not a silent {}", () => {
    expect(() => cache.set("k", new Map([["a", 1]]))).toThrow(/Map/);
  });

  it("rejects a Set with a clear message, not a silent {}", () => {
    expect(() => cache.set("k", new Set([1, 2]))).toThrow(/Set/);
  });

  it("rejects a Map nested inside an object", () => {
    expect(() => cache.set("k", { nested: new Map() })).toThrow(SerializationError);
  });

  it("rejects a circular reference", () => {
    const obj: Record<string, unknown> = { a: 1 };
    obj.self = obj;
    expect(() => cache.set("k", obj)).toThrow(SerializationError);
  });

  it("rejects NaN", () => {
    expect(() => cache.set("k", NaN)).toThrow(SerializationError);
  });

  it("rejects Infinity", () => {
    expect(() => cache.set("k", Infinity)).toThrow(SerializationError);
  });

  it("reading a Python-pickle-tagged row throws, never returns raw bytes", () => {
    cache.set("k", "placeholder");
    // Rewrite the row's type code to 5 (Python pickle) directly, simulating a file written by
    // another language's escape hatch.
    const db = (cache as unknown as { db: Database }).db;
    db.prepare("UPDATE cache SET value_type = 5 WHERE key = 'k'").run();
    expect(() => cache.get("k")).not.toThrow(); // strict: false degrades to a miss by default
    expect(cache.get("k")).toBeUndefined();
  });

  it("reading a Python-pickle-tagged row throws in strict mode", () => {
    const strictCache = new LyteCache({ path: cache.path, sweepInterval: null, strict: true });
    try {
      const db = (strictCache as unknown as { db: Database }).db;
      strictCache.set("k2", "placeholder");
      db.prepare("UPDATE cache SET value_type = 6 WHERE key = 'k2'").run();
      expect(() => strictCache.get("k2")).toThrow(SerializationError);
    } finally {
      strictCache.close();
    }
  });
});

describe("reviver and into hydration", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("applies a JSON.parse reviver", () => {
    cache.set("k", { when: "2024-01-15T10:30:00.000Z" });
    const result = cache.get<{ when: Date }>("k", undefined, {
      reviver: (key, value) => (key === "when" ? new Date(value as string) : value),
    });
    expect(result?.when).toBeInstanceOf(Date);
    expect(result?.when.getUTCFullYear()).toBe(2024);
  });

  it("rehydrates via 'into' with working prototype methods", () => {
    class Person {
      name!: string;
      greet(): string {
        return `hi, ${this.name}`;
      }
    }
    cache.set("k", { name: "Ada" });
    const person = cache.get<Person>("k", undefined, { into: Person });
    expect(person).toBeInstanceOf(Person);
    expect(person?.greet()).toBe("hi, Ada");
  });

  it("'into' shape mismatch throws in strict mode", () => {
    // Deserialize failures (including a caller-supplied 'into'/reviver mismatch) go through the
    // same strict/non-strict degrade-to-miss path as any other read, matching the Python
    // reference implementation's get(..., cls=) behavior.
    class Person {}
    const strictCache = new LyteCache({ path: cache.path, sweepInterval: null, strict: true });
    try {
      strictCache.set("k", [1, 2, 3]);
      expect(() => strictCache.get("k", undefined, { into: Person })).toThrow(SerializationError);
    } finally {
      strictCache.close();
    }
  });

  it("'into' shape mismatch degrades to a miss in non-strict (default) mode", () => {
    class Person {}
    cache.set("k", [1, 2, 3]);
    expect(cache.get("k", undefined, { into: Person })).toBeUndefined();
  });
});
