import { afterEach, beforeEach, describe, expect, it } from "vitest";
import { LyteCache } from "../src/index.js";
import { tempDbPath } from "./helpers.js";
import Database from "better-sqlite3";

describe("atomic counters", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("incr starts missing keys at 0", () => {
    expect(cache.incr("hits")).toBe(1);
    expect(cache.incr("hits")).toBe(2);
    expect(cache.incr("hits", 5)).toBe(7);
  });

  it("decr subtracts", () => {
    cache.set("hits", 10);
    expect(cache.decr("hits")).toBe(9);
    expect(cache.decr("hits", 3)).toBe(6);
  });

  it("incrFloat accumulates a float counter", () => {
    expect(cache.incrFloat("ratio", 1.5)).toBeCloseTo(1.5, 5);
    expect(cache.incrFloat("ratio", 2.5)).toBeCloseTo(4.0, 5);
  });

  it("throws TypeError when the existing value is not numeric", () => {
    cache.set("k", "not a number");
    expect(() => cache.incr("k")).toThrow(TypeError);
  });

  it("incrFloat is allowed against an existing integer counter", () => {
    cache.incr("k", 5);
    expect(cache.incrFloat("k", 0.5)).toBeCloseTo(5.5, 5);
  });

  it("incr against an existing float counter is rejected", () => {
    cache.incrFloat("k", 1.5);
    expect(() => cache.incr("k")).toThrow(TypeError);
  });

  it("stores the counter as plain decimal text, not a binary or float-suffixed form", () => {
    cache.incr("hits", 3);
    const db = new Database(cache.path, { readonly: true });
    try {
      const row = db
        .prepare("SELECT value, value_type FROM cache WHERE namespace = 'default' AND key = 'hits'")
        .get() as { value: Buffer; value_type: number };
      expect(row.value.toString("utf8")).toBe("3");
      expect(row.value_type).toBe(2);
    } finally {
      db.close();
    }
  });

  it("incr respects an existing ttl (does not reset it) unless the key was expired", async () => {
    cache.set("k", 1, { ttl: 60 });
    cache.incr("k");
    expect(cache.ttl("k")).toBeGreaterThan(0);
  });

  it("incr on an already-expired key resets it (starts fresh at amount)", async () => {
    cache.set("k", 100, { ttl: 0.01 });
    await new Promise((resolve) => setTimeout(resolve, 40));
    expect(cache.incr("k")).toBe(1);
  });

  describe("bigint / safe-integer boundary", () => {
    it("accepts a bigint amount beyond Number.MAX_SAFE_INTEGER", () => {
      const big = BigInt(Number.MAX_SAFE_INTEGER) + 100n;
      const result = cache.incr("big", big);
      expect(result).toBe(big);
      expect(typeof result).toBe("bigint");
    });

    it("returns a bigint once the running total exceeds Number.MAX_SAFE_INTEGER", () => {
      cache.set("big", Number.MAX_SAFE_INTEGER - 1);
      const first = cache.incr("big");
      expect(first).toBe(Number.MAX_SAFE_INTEGER);
      expect(typeof first).toBe("number");
      const second = cache.incr("big");
      expect(second).toBe(BigInt(Number.MAX_SAFE_INTEGER) + 1n);
      expect(typeof second).toBe("bigint");
    });

    it("rejects a plain number set() beyond MAX_SAFE_INTEGER", () => {
      expect(() => cache.set("k", Number.MAX_SAFE_INTEGER * 4)).toThrow();
    });

    it("accepts a bigint set() beyond MAX_SAFE_INTEGER and reads it back exactly", () => {
      const big = BigInt(Number.MAX_SAFE_INTEGER) * 1000n;
      cache.set("k", big);
      expect(cache.get("k")).toBe(big);
    });

    it("rejects a bigint beyond signed 64-bit range", () => {
      const tooBig = 2n ** 63n;
      expect(() => cache.set("k", tooBig)).toThrow();
    });
  });
});
