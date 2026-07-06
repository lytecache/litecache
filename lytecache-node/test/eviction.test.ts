import { describe, expect, it } from "vitest";
import { CacheFullError, LyteCache } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

function sleep(ms: number): Promise<void> {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

describe("eviction policies", () => {
  it("lru evicts the least-recently-used key first", async () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxKeys: 3,
      eviction: "lru",
      sweepInterval: null,
    });
    try {
      // last_accessed has millisecond resolution, so space writes out to avoid tie-breaking.
      cache.set("a", "1");
      await sleep(5);
      cache.set("b", "2");
      await sleep(5);
      cache.set("c", "3");
      await sleep(5);

      cache.get("a"); // 'a' is now most-recently-used
      await sleep(5);

      cache.set("d", "4"); // should evict 'b' (least-recently-used)

      expect(cache.exists("a")).toBe(true);
      expect(cache.exists("b")).toBe(false);
      expect(cache.exists("c")).toBe(true);
      expect(cache.exists("d")).toBe(true);
    } finally {
      cache.close();
    }
  });

  it("ttl eviction removes the soonest-to-expire key first", () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxKeys: 3,
      eviction: "ttl",
      sweepInterval: null,
    });
    try {
      cache.set("soon", "1", { ttl: 10 });
      cache.set("later", "2", { ttl: 1000 });
      cache.set("never", "3");
      cache.set("d", "4"); // over capacity; should evict 'soon' (nearest expiry)
      expect(cache.exists("soon")).toBe(false);
      expect(cache.exists("later")).toBe(true);
      expect(cache.exists("never")).toBe(true);
      expect(cache.exists("d")).toBe(true);
    } finally {
      cache.close();
    }
  });

  it("random eviction keeps the dataset within maxKeys", () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxKeys: 3,
      eviction: "random",
      sweepInterval: null,
    });
    try {
      cache.set("a", "1");
      cache.set("b", "2");
      cache.set("c", "3");
      cache.set("d", "4");
      expect(cache.stats().keyCount).toBe(3);
    } finally {
      cache.close();
    }
  });

  it("noeviction rejects the write outright, with no side effect", () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxKeys: 3,
      eviction: "noeviction",
      sweepInterval: null,
    });
    try {
      cache.set("a", "1");
      cache.set("b", "2");
      cache.set("c", "3");
      expect(() => cache.set("d", "4")).toThrow(CacheFullError);
      // The rejected write must not have landed in the table.
      expect(cache.exists("d")).toBe(false);
      expect(cache.stats().keyCount).toBe(3);
    } finally {
      cache.close();
    }
  });

  it("noeviction still allows overwriting an existing key", () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxKeys: 2,
      eviction: "noeviction",
      sweepInterval: null,
    });
    try {
      cache.set("a", "1");
      cache.set("b", "2");
      expect(() => cache.set("a", "updated")).not.toThrow();
      expect(cache.get("a")).toBe("updated");
    } finally {
      cache.close();
    }
  });

  it("maxBytes evicts once the namespace exceeds the byte budget", async () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxBytes: 100,
      eviction: "lru",
      sweepInterval: null,
    });
    try {
      cache.set("small", "x");
      await sleep(5);
      cache.set("large", "y".repeat(50));
      await sleep(5);
      cache.set("xlarge", "z".repeat(60));
      expect(cache.stats().sizeBytes).toBeLessThan(100);
    } finally {
      cache.close();
    }
  });

  it("the sweeper also enforces capacity, not just set()", async () => {
    const cache = new LyteCache({
      path: tempDbPath(),
      maxKeys: 3,
      eviction: "lru",
      sweepInterval: 0.05,
    });
    try {
      cache.set("a", "1");
      cache.set("b", "2");
      cache.set("c", "3");
      cache.set("d", "4");
      await sleep(150);
      expect(cache.stats().keyCount).toBe(3);
    } finally {
      cache.close();
    }
  });
});
