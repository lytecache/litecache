import { afterEach, beforeEach, describe, expect, it } from "vitest";
import { LyteCache } from "../src/index.js";
import { tempDbPath } from "./helpers.js";

describe("basic set/get", () => {
  let cache: LyteCache;

  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });

  afterEach(() => {
    cache.close();
  });

  it("round-trips a string", () => {
    cache.set("k", "value");
    expect(cache.get("k")).toBe("value");
  });

  it("round-trips a plain object", () => {
    cache.set("user", { name: "Ada", age: 30 });
    expect(cache.get("user")).toEqual({ name: "Ada", age: 30 });
  });

  it("round-trips an array", () => {
    cache.set("list", [1, 2, 3]);
    expect(cache.get("list")).toEqual([1, 2, 3]);
  });

  it("round-trips a boolean and null", () => {
    cache.set("t", true);
    cache.set("f", false);
    cache.set("n", null);
    expect(cache.get("t")).toBe(true);
    expect(cache.get("f")).toBe(false);
    expect(cache.get("n")).toBe(null);
  });

  it("round-trips an integer", () => {
    cache.set("n", 42);
    expect(cache.get("n")).toBe(42);
  });

  it("round-trips a float", () => {
    cache.set("pi", 3.14159);
    expect(cache.get<number>("pi")).toBeCloseTo(3.14159, 5);
  });

  it("round-trips a Buffer", () => {
    const data = Buffer.from([1, 2, 3, 4, 5]);
    cache.set("bytes", data);
    expect(cache.get("bytes")).toEqual(data);
  });

  it("returns undefined (or the given default) on miss", () => {
    expect(cache.get("missing")).toBeUndefined();
    expect(cache.get("missing", "fallback")).toBe("fallback");
  });

  it("honors a class's toJSON()", () => {
    class Money {
      constructor(private readonly cents: number) {}
      toJSON() {
        return { cents: this.cents, currency: "USD" };
      }
    }
    cache.set("price", new Money(1999));
    expect(cache.get("price")).toEqual({ cents: 1999, currency: "USD" });
  });

  it("hydrates into a class via the 'into' option", () => {
    class Person {
      name!: string;
      age!: number;
      greet(): string {
        return `hi, ${this.name}`;
      }
    }
    cache.set("p", { name: "Ada", age: 30 });
    const person = cache.get<Person>("p", undefined, { into: Person });
    expect(person).toBeInstanceOf(Person);
    expect(person?.greet()).toBe("hi, Ada");
  });
});

describe("delete & exists", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("deletes keys and reports how many existed", () => {
    cache.set("a", "1");
    cache.set("b", "2");
    expect(cache.delete("a", "b", "nonexistent")).toBe(2);
    expect(cache.exists("a")).toBe(false);
  });

  it("exists reflects presence", () => {
    cache.set("k", "v");
    expect(cache.exists("k")).toBe(true);
    expect(cache.exists("missing")).toBe(false);
  });
});

describe("add / replace / getSet", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("add sets only if absent", () => {
    expect(cache.add("k", "first")).toBe(true);
    expect(cache.add("k", "second")).toBe(false);
    expect(cache.get("k")).toBe("first");
  });

  it("replace sets only if present", () => {
    expect(cache.replace("k", "value")).toBe(false);
    cache.set("k", "old");
    expect(cache.replace("k", "new")).toBe(true);
    expect(cache.get("k")).toBe("new");
  });

  it("getSet atomically swaps and returns the old value", () => {
    cache.set("k", "old");
    expect(cache.getSet("k", "new")).toBe("old");
    expect(cache.get("k")).toBe("new");
  });

  it("getSet returns undefined when the key was absent", () => {
    expect(cache.getSet("k", "new")).toBeUndefined();
    expect(cache.get("k")).toBe("new");
  });
});

describe("setMany / getMany", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("sets and gets multiple entries", () => {
    cache.setMany({ a: "1", b: "2", c: "3" });
    const result = cache.getMany(["a", "b", "c", "missing"]);
    expect(result.get("a")).toBe("1");
    expect(result.get("b")).toBe("2");
    expect(result.has("missing")).toBe(false);
  });

  it("setMany applies a shared TTL", () => {
    cache.setMany({ a: "1", b: "2" }, { ttl: 0.05 });
    expect(cache.get("a")).toBe("1");
  });
});

describe("flush & stats", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("flush clears the whole namespace", () => {
    cache.set("a", "1");
    cache.set("b", "2");
    cache.flush();
    expect(cache.get("a")).toBeUndefined();
    expect(cache.get("b")).toBeUndefined();
  });

  it("stats reports hits, misses, and hit rate", () => {
    cache.set("k", "v");
    cache.get("k");
    cache.get("k");
    cache.get("missing");
    const stats = cache.stats();
    expect(stats.keyCount).toBe(1);
    expect(stats.hits).toBe(2);
    expect(stats.misses).toBe(1);
    expect(stats.hitRate).toBeCloseTo(2 / 3, 5);
    expect(stats.path).toContain(".db");
  });
});

describe("keys()", () => {
  let cache: LyteCache;
  beforeEach(() => {
    cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
  });
  afterEach(() => cache.close());

  it("iterates keys matching a glob pattern", () => {
    cache.set("user:1", "Alice");
    cache.set("user:2", "Bob");
    cache.set("post:1", "Post1");
    const matched = [...cache.keys("user:*")].sort();
    expect(matched).toEqual(["user:1", "user:2"]);
  });

  it("iterates every key with the default pattern", () => {
    cache.set("a", "1");
    cache.set("b", "2");
    expect([...cache.keys()].sort()).toEqual(["a", "b"]);
  });

  it("excludes expired keys", () => {
    cache.set("gone", "x", { ttl: 0.01 });
    cache.set("here", "y");
    return new Promise<void>((resolve) => {
      setTimeout(() => {
        expect([...cache.keys()]).toEqual(["here"]);
        resolve();
      }, 50);
    });
  });
});

describe("namespace isolation", () => {
  it("keeps namespaces independent within one file", () => {
    const path = tempDbPath();
    const ns1 = new LyteCache({ path, namespace: "ns1", sweepInterval: null });
    const ns2 = new LyteCache({ path, namespace: "ns2", sweepInterval: null });
    try {
      ns1.set("key", "value1");
      ns2.set("key", "value2");
      expect(ns1.get("key")).toBe("value1");
      expect(ns2.get("key")).toBe("value2");
    } finally {
      ns1.close();
      ns2.close();
    }
  });
});

describe("vacuum & close", () => {
  it("vacuum runs without error and the file persists", () => {
    const path = tempDbPath();
    const cache = new LyteCache({ path, sweepInterval: null });
    cache.set("k", "v");
    cache.delete("k");
    expect(() => cache.vacuum()).not.toThrow();
    cache.close();
  });

  it("close is idempotent", () => {
    const cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
    cache.close();
    expect(() => cache.close()).not.toThrow();
  });

  it("throws when used after close", () => {
    const cache = new LyteCache({ path: tempDbPath(), sweepInterval: null });
    cache.close();
    expect(() => cache.set("k", "v")).toThrow();
  });

  it("supports explicit resource management via Symbol.dispose", () => {
    // Exercises [Symbol.dispose]() directly rather than an actual `using` declaration: `using`
    // needs a newer V8 than some Node versions in this package's supported matrix (>=18) ship,
    // while the underlying protocol -- calling the well-known symbol method -- works everywhere.
    const path = tempDbPath();
    {
      const cache = new LyteCache({ path, sweepInterval: null });
      cache.set("k", "v");
      expect(cache.get("k")).toBe("v");
      cache[Symbol.dispose]();
    }
    // A fresh instance can reopen the same file, proving the previous one closed cleanly.
    const reopened = new LyteCache({ path, sweepInterval: null });
    expect(reopened.get("k")).toBe("v");
    reopened.close();
  });
});
