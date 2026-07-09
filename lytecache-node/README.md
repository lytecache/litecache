# lytecache

Redis-like caching for Node.js with zero infrastructure -- no server, just a file. `lytecache` gives you the familiar Redis API surface -- `set`/`get`, TTLs, atomic counters, eviction, distributed locks -- backed by a local SQLite file instead of a daemon. No connection pool, no port to open, no client to configure.

## Install

```bash
npm install lytecache
```

## Quickstart

```ts
import { LyteCache } from "lytecache";

const cache = new LyteCache(); // no path, no setup -- just works
cache.set("user:42", { name: "Samson" }, { ttl: 300 });
cache.get("user:42"); // { name: "Samson" }
cache.incr("hits"); // 1
```

That's it. The first call to `new LyteCache()` creates the database file (including any missing parent directories) and applies the schema automatically. There's no `init()`, no migration step, and no server to start. `cache.close()` is optional -- safe to call, and safe to skip for the lifetime of a long-running process (see [Why synchronous?](#why-synchronous) for what actually keeps the process alive and what doesn't).

## API

| Method | Description |
|---|---|
| `set(key, value, { ttl })` | Store a value, optionally with a TTL in **seconds**. |
| `get(key, defaultValue?, options?)` | Read a value; returns `defaultValue` (default `undefined`) on miss or expiry. Never throws on miss. `options.reviver`/`options.into` refine JSON reads. |
| `delete(...keys)` | Delete keys; returns the number actually deleted. |
| `exists(key)` | Whether a (non-expired) key is present. |
| `add(key, value, { ttl })` | Set only if absent (atomic `SET NX`). |
| `replace(key, value, { ttl })` | Set only if present (atomic `SET XX`). |
| `getSet(key, value)` | Atomically swap in a new value, returning the old one. |
| `setMany(entries, { ttl })` / `getMany(keys)` | Bulk set/get in a single transaction; `getMany` returns a `Map`. |
| `expire(key, ttlSeconds)` / `persist(key)` | Set or remove a TTL on an existing key. |
| `ttl(key)` | Seconds remaining, `-1` if no expiry, `undefined` if missing. |
| `touch(key, ttlSeconds)` | Refresh a key's TTL (sliding expiration). |
| `incr(key, amount = 1)` / `decr(key, amount = 1)` | Atomic integer counters. Returns `number`, or `bigint` beyond `Number.MAX_SAFE_INTEGER`. |
| `incrFloat(key, amount)` | Atomic float counter. |
| `keys(pattern = "*")` | Lazily iterate matching keys (`GLOB` syntax: `*`, `?`, `[...]`). |
| `flush()` | Clear the current namespace. |
| `stats()` | Hits, misses, hit rate, key count, size, evictions, path. |
| `vacuum()` / `close()` | Reclaim disk space / shut down cleanly. |
| `memoize(key, ttlSeconds, loader)` / `memoizeAsync(...)` | Read-through cache for a computed value. |
| `wrap(fn, { ttl })` | Returns a memoized version of a function. |
| `lock(name, { timeoutMs, pollMs })` | Process-safe distributed lock; disposable via `.release()` or `Symbol.dispose`. |

`LyteCache` and the object returned by `lock()` both implement `Symbol.dispose`, so `using cache = new LyteCache()` works on a new enough Node/TypeScript.

## Why synchronous?

Every method here is synchronous -- no `Promise`, no `await`. That's a deliberate choice, not a stopgap:

- **better-sqlite3 is already synchronous** under the hood; wrapping it in `Promise`s would only add microtask overhead without buying you real concurrency, since SQLite access from one process is inherently serialized anyway.
- **A local file has no network latency to hide.** The entire reason JS APIs are usually async is to avoid blocking the event loop on I/O that takes milliseconds-to-seconds (disk, network). A `get()` here takes low single-digit microseconds -- there's nothing to hide.
- **It makes the API honest.** `cache.incr("hits")` being synchronous means you can use it exactly like an in-memory counter, in a hot loop, without `await` noise or accidentally interleaving two calls on the same key.

The trade-off: a `get()`/`set()` call does block the event loop for its (very short) duration, same as `fs.readFileSync` does. For a cache -- typically called from otherwise-synchronous logic, not from inside a tight async I/O loop -- this is the right trade.

## Where is my data?

By default, `new LyteCache()` stores its file at:

```
<platform cache dir>/lytecache/<project-id>.db
```

- **Linux**: `$XDG_CACHE_HOME/lytecache/<project-id>.db`, or `~/.cache/lytecache/<project-id>.db`
- **macOS**: `~/Library/Caches/lytecache/<project-id>.db`
- **Windows**: `%LOCALAPPDATA%\lytecache\<project-id>.db`

`<project-id>` is the first 12 hex characters of the SHA-256 hash of your current working directory's resolved, absolute path -- identical to the Python and Java implementations' derivation, so every project gets its own file automatically, and a Node process and a Python/Java process started from the same directory share one cache.

```ts
LyteCache.defaultPath(); // -> string, the resolved default location
cache.path; // -> string, this instance's actual file
cache.stats().path; // the file is never a mystery
```

Override it explicitly:

```ts
const cache = new LyteCache({ path: "/data/cache.db" }); // explicit escape hatch
```

```bash
export LYTECACHE_PATH=/data/cache.db  # takes priority over the default
```

## When to use lytecache

**Good fit:**
- Single-node Node apps (or single-machine, multi-process apps -- PM2 clusters, worker pools) that want caching, counters, or TTLs with zero infrastructure.
- CLIs, scripts, small services, background jobs, test fixtures.
- A cache that survives process restarts without running a separate daemon.
- Multi-process coordination via the process-safe distributed lock.
- Mixed-language systems where a Node process needs to share a cache file with a Python or Java one.

**Not a good fit:**
- A cache shared live across multiple servers/hosts -- SQLite is a local file, not a network service. Use Redis/Memcached.
- Heavy concurrent write throughput from many processes -- SQLite's single-writer model will serialize writes and become a bottleneck.
- Pub/sub, streams, or other Redis data structures beyond key-value + counters -- lytecache intentionally stays small.

## Configuration reference

```ts
new LyteCache({
  path: undefined, // explicit file path; default: LyteCache.defaultPath()
  namespace: "default", // logical partition within the database file
  maxKeys: undefined, // evict when the namespace exceeds this many keys
  maxBytes: undefined, // evict when the namespace exceeds this many bytes
  eviction: "lru", // "lru" | "ttl" | "random" | "noeviction"
  sweepInterval: 60, // seconds between background maintenance passes;
  // null disables the timer and sweeps opportunistically
  // every ~100 operations instead
  strict: false, // true: throw on internal read errors instead of
  // degrading to a miss
  logger: console.warn, // called with a warning message on a non-strict
  // degraded read; pass a no-op to silence it
});
```

- **Eviction policies**: `lru` (default, evicts least-recently-used), `ttl` (soonest-to-expire first), `random`, and `noeviction` (throws `CacheFullError` instead of evicting). LFU is a documented TODO.
- **Errors**: `LyteCacheError` (base), `CacheFullError`, `SerializationError`, `SchemaVersionError`, `LockTimeoutError` -- all exported.
- **Concurrency**: safe across processes sharing one file (SQLite WAL mode + atomic single-statement writes). `stats()` counters (hits/misses/etc.) are per-process, not shared cluster-wide.

See [SPEC.md](SPEC.md) for the on-disk schema, type codes, and full cross-language semantics.

## License

Apache License 2.0. See [LICENSE](LICENSE).
