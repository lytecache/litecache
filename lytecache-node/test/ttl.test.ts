import { afterEach, beforeEach, describe, expect, it } from "vitest";
import { LyteCache } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

describe("TTL semantics", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("no ttl means no expiry", () => {
    cache.set("k", "v");
    expect(cache.ttl("k")).toBe(-1);
  });

  it("ttl of a missing key is undefined", () => {
    expect(cache.ttl("missing")).toBeUndefined();
  });

  it("ttl reports seconds remaining", () => {
    cache.set("k", "v", { ttl: 10 });
    const remaining = cache.ttl("k");
    expect(remaining).toBeGreaterThan(9);
    expect(remaining).toBeLessThanOrEqual(10);
  });

  it("ttl of 0 expires immediately", async () => {
    cache.set("k", "v", { ttl: 0 });
    await sleep(5);
    expect(cache.get("k")).toBeUndefined();
  });

  it("negative ttl expires immediately", async () => {
    cache.set("k", "v", { ttl: -5 });
    await sleep(5);
    expect(cache.get("k")).toBeUndefined();
  });

  it("fractional-second ttl works", async () => {
    cache.set("k", "v", { ttl: 0.05 });
    expect(cache.get("k")).toBe("v");
    await sleep(100);
    expect(cache.get("k")).toBeUndefined();
  });

  it("lazily expires on read without a sweeper", async () => {
    cache.set("k", "v", { ttl: 0.02 });
    await sleep(60);
    expect(cache.get("k")).toBeUndefined();
    expect(cache.exists("k")).toBe(false);
  });

  it("expire() sets a ttl on an existing key", () => {
    cache.set("k", "v");
    expect(cache.expire("k", 60)).toBe(true);
    expect(cache.ttl("k")).toBeGreaterThan(0);
  });

  it("expire() on a missing key returns false", () => {
    expect(cache.expire("missing", 60)).toBe(false);
  });

  it("expire() on an already-expired key returns false", async () => {
    cache.set("k", "v", { ttl: 0.01 });
    await sleep(40);
    expect(cache.expire("k", 60)).toBe(false);
  });

  it("persist() removes a ttl", () => {
    cache.set("k", "v", { ttl: 60 });
    expect(cache.persist("k")).toBe(true);
    expect(cache.ttl("k")).toBe(-1);
  });

  it("persist() on a key without a ttl returns false", () => {
    cache.set("k", "v");
    expect(cache.persist("k")).toBe(false);
  });

  it("persist() on a missing key returns false", () => {
    expect(cache.persist("missing")).toBe(false);
  });

  it("touch() refreshes a ttl (sliding expiration)", async () => {
    cache.set("k", "v", { ttl: 0.1 });
    await sleep(50);
    expect(cache.touch("k", 0.2)).toBe(true);
    await sleep(100);
    // Would have expired under the original 0.1s ttl by now, but touch() refreshed it.
    expect(cache.get("k")).toBe("v");
  });

  it("active expiration: the sweeper removes expired rows even without a read", async () => {
    const path = tempDbPath();
    const swept = new LyteCache({ path, sweepInterval: 0.05 });
    try {
      swept.set("k", "v", { ttl: 0.01 });
      await sleep(150);
      const stats = swept.stats();
      expect(stats.expiredRemoved).toBeGreaterThanOrEqual(1);
    } finally {
      swept.close();
    }
  });
});
