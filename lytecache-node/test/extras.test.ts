import { afterEach, beforeEach, describe, expect, it, vi } from "vitest";
import { LyteCache, LockTimeoutError } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

describe("memoize / memoizeAsync / wrap", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("memoize only calls the loader once for a given key", () => {
    const loader = vi.fn(() => ({ computed: true }));
    const first = cache.memoize("k", 60, loader);
    const second = cache.memoize("k", 60, loader);
    expect(first).toEqual({ computed: true });
    expect(second).toEqual({ computed: true });
    expect(loader).toHaveBeenCalledTimes(1);
  });

  it("memoizeAsync only calls the loader once for a given key", async () => {
    const loader = vi.fn(async () => "value");
    const first = await cache.memoizeAsync("k", 60, loader);
    const second = await cache.memoizeAsync("k", 60, loader);
    expect(first).toBe("value");
    expect(second).toBe("value");
    expect(loader).toHaveBeenCalledTimes(1);
  });

  it("wrap memoizes a function by its arguments", () => {
    const fn = vi.fn((a: number, b: number) => a + b);
    const wrapped = cache.wrap(fn, { ttl: 60 });
    expect(wrapped(1, 2)).toBe(3);
    expect(wrapped(1, 2)).toBe(3);
    expect(wrapped(2, 3)).toBe(5);
    expect(fn).toHaveBeenCalledTimes(2); // once for (1,2), once for (2,3)
  });
});

describe("lock", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("acquires and releases", () => {
    const lock = cache.lock("resource");
    expect(cache.exists("lock:resource")).toBe(true);
    lock.release();
    expect(cache.exists("lock:resource")).toBe(false);
  });

  it("throws LockTimeoutError when already held", () => {
    const lock1 = cache.lock("resource", { timeoutMs: 5000 });
    try {
      expect(() => cache.lock("resource", { timeoutMs: 100, pollMs: 20 })).toThrow(
        LockTimeoutError,
      );
    } finally {
      lock1.release();
    }
  });

  it("release only removes a lock this instance still owns", () => {
    const lock1 = cache.lock("owned", { timeoutMs: 1000 });
    // Simulate lock1's TTL expiring and someone else acquiring it in the meantime.
    cache.delete("lock:owned");
    const lock2 = cache.lock("owned", { timeoutMs: 1000 });
    lock1.release(); // must be a no-op: lock1's token no longer matches the stored value
    expect(cache.exists("lock:owned")).toBe(true);
    lock2.release();
  });

  it("supports Symbol.dispose", () => {
    const lock = cache.lock("resource");
    expect(cache.exists("lock:resource")).toBe(true);
    lock[Symbol.dispose]();
    expect(cache.exists("lock:resource")).toBe(false);
  });
});
