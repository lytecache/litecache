# lytecache

[![Python](https://img.shields.io/badge/Python-3776AB?style=flat&logo=python&logoColor=white)](lytecache-python/)
[![Java](https://img.shields.io/badge/Java-E76F00?style=flat&logo=openjdk&logoColor=white)](lytecache-java/)
[![Node.js](https://img.shields.io/badge/Node.js-339933?style=flat&logo=node.js&logoColor=white)](lytecache-node/)
[![License: Apache 2.0](https://img.shields.io/badge/License-Apache%202.0-blue.svg)](LICENSE)

**Redis-like caching with zero infrastructure — no server, just a local SQLite file.**

`lytecache` gives you the familiar Redis API surface — `set`/`get`, TTLs, atomic counters, eviction, distributed locks — backed by a single portable SQLite file instead of a daemon. No server to run, no port to open, no client to configure. Just add the dependency and go.

This is **not a library for one language**. It's a single storage spec — one on-disk schema, one set of semantics — implemented natively in every popular programming language, so the same cache file can be read and written from whichever language each part of your system happens to use.

This repository is a **monorepo** containing independent, same-spec implementations:

| Package | Language | Install | Docs |
|---|---|---|---|
| [`lytecache-python/`](lytecache-python/) | ![Python](https://img.shields.io/badge/-Python-3776AB?style=flat-square&logo=python&logoColor=white) Python 3.9+ | `pip install lytecache` | [lytecache-python/README.md](lytecache-python/README.md) |
| [`lytecache-java/`](lytecache-java/) | ![Java](https://img.shields.io/badge/-Java-E76F00?style=flat-square&logo=openjdk&logoColor=white) Java 17+ | `io.lytecache:lytecache` (Gradle/Maven) | [lytecache-java/README.md](lytecache-java/README.md) |
| [`lytecache-node/`](lytecache-node/) | ![Node.js](https://img.shields.io/badge/-Node.js-339933?style=flat-square&logo=node.js&logoColor=white) Node.js 18+ | `npm install lytecache` | [lytecache-node/README.md](lytecache-node/README.md) |

More languages are expected to join this list over time — the storage spec below is the contract any implementation needs to satisfy. They share one on-disk [storage spec](#storage-spec--cross-language-compatibility): a cache file written by one is readable — and, for counters, atomically incrementable — by any of the others. Everything below is a quick tour; each package's own README is the full reference for that language.

## Quickstart

<table>
<tr><th>Python</th><th>Java</th><th>Node.js</th></tr>
<tr valign="top">
<td>

```python
from lytecache import LyteCache

cache = LyteCache()            # no path, no setup
cache.set("user:42", {"name": "Ada"}, ttl=300)
cache.get("user:42")           # {"name": "Ada"}
cache.incr("hits")             # 1
```

</td>
<td>

```java
import io.lytecache.LyteCache;
import java.time.Duration;

try (LyteCache cache = new LyteCache()) {
    cache.set("user:42", "Ada", Duration.ofMinutes(5));
    cache.getString("user:42");  // "Ada"
    cache.incr("hits");          // 1
}
```

</td>
<td>

```ts
import { LyteCache } from "lytecache";

const cache = new LyteCache();  // no path, no setup
cache.set("user:42", { name: "Ada" }, { ttl: 300 });
cache.get("user:42");           // { name: "Ada" }
cache.incr("hits");             // 1
```

</td>
</tr>
</table>

That's it in every language: the first call creates the database file (including any missing parent directories) and applies the schema automatically. There is no `init()`, no migration step, and no server to start.

## Where is my data?

Every implementation resolves the **same default file** for the same project, using the same derivation, so a Python process, a Java process, and a Node.js process started from the same working directory share one cache automatically:

```
<platform cache dir>/lytecache/<project-id>.db
```

- **Linux**: `$XDG_CACHE_HOME/lytecache/<project-id>.db`, or `~/.cache/lytecache/<project-id>.db`
- **macOS**: `~/Library/Caches/lytecache/<project-id>.db`
- **Windows**: `%LOCALAPPDATA%\lytecache\<project-id>.db`

`<project-id>` is the first 12 hex characters of the SHA-256 hash of your current working directory's resolved, absolute path — identical across every implementation, so every project gets its own file automatically and nothing is left behind in your repo.

Override it the same way in any language:
- Pass an explicit path (`LyteCache("/data/cache.db")` in Python, `.path(Path.of("/data/cache.db"))` on the Java builder, `new LyteCache({ path: "/data/cache.db" })` in Node.js).
- Set `LYTECACHE_PATH=/data/cache.db` in the environment — takes priority over the default in all of them.

Every implementation exposes the resolved path programmatically (`LyteCache.default_path()` / `cache.path` in Python; `LyteCache.defaultPath()` / `cache.path()` in Java; `LyteCache.defaultPath()` / `cache.path` in Node.js) — the file is never a mystery.

## API at a glance

Every implementation covers the same operations; naming follows each language's conventions (`snake_case` vs `camelCase`, `ttl=` vs `Duration` vs `{ ttl }`).

| Operation | Python | Java | Node.js |
|---|---|---|---|
| Set / get | `cache.set(key, value, ttl=None)` / `cache.get(key, default=None)` | `cache.set(key, value, ttl)` / `cache.get(key, Type.class)` | `cache.set(key, value, { ttl })` / `cache.get(key, default)` |
| Typed convenience getters | `get(key, default)` returns native type | `getString` / `getLong` / `getDouble` / `getBytes` | `get(key, default, { into })` rehydrates a class |
| Delete / exists | `delete(*keys)` / `exists(key)` | `delete(String... keys)` / `exists(key)` | `delete(...keys)` / `exists(key)` |
| Set-if-absent / set-if-present (<abbr title="only if Not eXists">NX</abbr> / <abbr title="only if it already eXists">XX</abbr>) | `add(key, value, ttl)` / `replace(key, value, ttl)` | `add(key, value, ttl)` / `replace(key, value, ttl)` | `add(key, value, { ttl })` / `replace(key, value, { ttl })` |
| Atomic swap | `get_set(key, value)` | `getSet(key, value)` | `getSet(key, value)` |
| Bulk set / get | `set_many(mapping, ttl)` / `get_many(keys)` | `setAll(Map, ttl)` / `getAll(Collection)` | `setMany(entries, { ttl })` / `getMany(keys)` |
| Expiration | `expire(key, ttl)` / `persist(key)` / `ttl(key)` / `touch(key, ttl)` | `expire(key, ttl)` / `persist(key)` / `ttl(key)` / `touch(key, ttl)` | `expire(key, ttl)` / `persist(key)` / `ttl(key)` / `touch(key, ttl)` |
| Atomic counters | `incr(key, amount=1)` / `decr(...)` / `incr_float(key, amount)` | `incr(key)` / `decr(key)` / `incrDouble(key, amount)` | `incr(key, amount)` / `decr(key, amount)` / `incrFloat(key, amount)` |
| Key scanning (GLOB pattern) | `keys(pattern="*")` (lazy iterator) | `keys(pattern)` (lazy `Stream<String>`) | `keys(pattern)` (lazy generator) |
| Clear / stats | `flush()` / `stats()` | `flush()` / `stats()` | `flush()` / `stats()` |
| Maintenance | `vacuum()` / `close()` | `vacuum()` / `close()` | `vacuum()` / `close()` |
| Read-through cache | `@cache.memoize(ttl=None)` decorator | `cache.memoize(key, ttl, loader)` | `cache.memoize(key, ttl, loader)` |
| Distributed lock | `with cache.lock(name, timeout=30): ...` | `try (CacheLock l = cache.lock(name, timeout)) { ... }` | `using lock = cache.lock(name, { timeoutMs })` |

Two easy-to-miss details that come up often:
- **`flush()` takes no key/pattern argument** — it always deletes everything in the current namespace. To clear a subset, delete by key or pattern instead: `delete(*keys)` / `cache.delete(String... keys)` / `cache.delete(...keys)`, or iterate `keys(pattern)` and delete each match.
- **Python's and Node's `ttl` are both plain numbers, in seconds** (`ttl=5000` / `{ ttl: 5000 }` means ~83 minutes, not 5 seconds); **Java's is an explicit `Duration`** (`Duration.ofSeconds(5)` vs `Duration.ofMillis(5)`), so there's no unit ambiguity there. In every language, expiration is enforced both lazily (on every read) and actively (a background sweeper) — see each package's README for details.

All three are disposable:

```python
with LyteCache() as cache:
    cache.set("k", "v")
```

```java
try (LyteCache cache = new LyteCache()) {
    cache.set("k", "v");
}
```

```ts
using cache = new LyteCache();
cache.set("k", "v");
```

See each package's README for full method signatures, configuration options, and serialization rules (values are stored as portable JSON so complex objects round-trip across every implementation — see [Storage spec](#storage-spec--cross-language-compatibility)).

## When to use lytecache

**Good fit:**
- Single-node apps (or single-machine, multi-process apps) that want caching, counters, or TTLs with zero infrastructure.
- Scripts, CLIs, notebooks, small web services, background jobs, test fixtures.
- A cache that needs to survive process restarts without running a separate daemon.
- Multi-process coordination via the process-safe distributed lock.
- Mixed-language systems — a Python, Java, and Node.js process can all share one cache file.

**Not a good fit:**
- A cache shared live across multiple servers/hosts — SQLite is a local file, not a network service. Use Redis/Memcached.
- Heavy concurrent write throughput from many processes — SQLite's single-writer model will serialize writes and become a bottleneck.
- Pub/sub, streams, or other Redis data structures beyond key-value + counters — lytecache intentionally stays small.
- Complex queries over cached data — use a real database.

## Storage spec & cross-language compatibility

Every implementation reads and writes the same schema and value encoding, documented as a versioned spec in each package (kept in sync): [lytecache-python/SPEC.md](lytecache-python/SPEC.md), [lytecache-java/SPEC.md](lytecache-java/SPEC.md), [lytecache-node/SPEC.md](lytecache-node/SPEC.md). In short:

- One SQLite file, WAL mode, `PRAGMA busy_timeout=5000` so cross-process/cross-thread contention waits instead of failing.
- Every value is tagged with a `value_type` code: `0` bytes, `1` UTF-8 string, `2` int (UTF-8 decimal text, not binary — this is what lets `incr`/`decr` be a single atomic SQL UPSERT in every language), `3` float (UTF-8 decimal text), `4` JSON (the format for any object/dict/list/dataclass/POJO/record).
- Codes `5` (Python pickle) and `6` (Java native serialization) are language-specific escape hatches that are **never** used for the portable path — reading one from another language raises a clear serialization error instead of returning garbage.
- The zero-config default path derivation (`<project-id>` = SHA-256 of the resolved cwd) is byte-for-byte identical across every implementation, so all of them land on the same file for the same project directory.
- Node.js has a single `number` type rather than Python's `int`/`float` or Java's `long`/`double`, so it picks type code `2` vs `3` by shape (`Number.isInteger()`) rather than by caller intent, using `bigint` for integers beyond `Number.MAX_SAFE_INTEGER`. See [lytecache-node/SPEC.md](lytecache-node/SPEC.md) for the full rules.

## Glossary

A handful of abbreviations show up above without being spelled out inline. Here's what each one means:

| Term | Full name | What it means here |
|---|---|---|
| API | Application Programming Interface | The set of methods a library exposes for other code to call. |
| CLI | Command-Line Interface | A program you run and control from a terminal. |
| TTL | Time To Live | How long a cached value is kept before it's treated as expired. |
| LRU | Least Recently Used | Eviction policy: when the cache is full, remove the key that hasn't been read or written in the longest time. |
| LFU | Least Frequently Used | Eviction policy that removes the key with the fewest accesses. Documented as a future addition, not yet implemented. |
| SQL | Structured Query Language | The language used to talk to SQLite — every read and write compiles to one SQL statement. |
| WAL | Write-Ahead Logging | A SQLite journaling mode that lets readers and a writer access the file concurrently without blocking each other. |
| JSON | JavaScript Object Notation | The plain-text format used to store objects/arrays/etc. portably across every language. |
| POJO | Plain Old Java Object | An ordinary Java object with no special base class or framework annotation required. |
| GLOB | Global (pattern matching) | Shell-style wildcard syntax (`*`, `?`, `[...]`) used by `keys(pattern)`. |
| NX / XX | Not eXists / already eXists | Redis-style flag names: `add()` behaves like `SET NX`, `replace()` like `SET XX`. |
| UPSERT | Update + Insert | A single SQL statement that inserts a new row or updates the existing one — used so counters and locks stay atomic under concurrency. |
| CJS / ESM | CommonJS / ECMAScript Modules | Node.js's two module systems (`require()` vs `import`). The Node.js package ships both. |
| SHA-256 | Secure Hash Algorithm, 256-bit | A one-way fingerprint function used to derive each project's default cache filename from its working directory. |

## Developing this repo

Each package builds independently:

```bash
# Python
cd lytecache-python
pip install -e ".[dev]"   # or: uv sync
pytest

# Java
cd lytecache-java
./gradlew build           # compiles, runs tests, generates javadoc
./gradlew publishToMavenLocal

# Node.js
cd lytecache-node
npm install
npm run build && npm test
```

See [lytecache-python/README.md](lytecache-python/README.md), [lytecache-java/README.md](lytecache-java/README.md), and [lytecache-node/README.md](lytecache-node/README.md) for full configuration references, and each package's `CHANGELOG.md` for release notes.

## License

Apache License 2.0. See [LICENSE](LICENSE).
