# lytecache

Redis-like caching with zero infrastructure. `lytecache` gives you the
familiar Redis API surface -- `set`/`get`, TTLs, atomic counters, eviction --
backed by a local SQLite file instead of a server. No daemon to run, no port
to open, no client library to configure. Just `pip install` and go.

## Install

```bash
pip install lytecache
```

## Quickstart

```python
from lytecache import LyteCache

cache = LyteCache()                    # no path, no setup -- just works
cache.set("user:42", {"name": "Samson"}, ttl=300)
cache.get("user:42")                   # {"name": "Samson"}
cache.incr("hits")                     # 1
cache.get("missing", "default")        # "default"
```

That's it. The first call to `LyteCache()` creates the database file
(including any missing parent directories) and applies the schema
automatically. There is no `init()`, no migration step, and no server to
start.

## Where is my data?

By default, `LyteCache()` stores its file at:

```
<platform cache dir>/lytecache/<project-id>.db
```

- **Linux**: `$XDG_CACHE_HOME/lytecache/<project-id>.db`, or `~/.cache/lytecache/<project-id>.db`
- **macOS**: `~/Library/Caches/lytecache/<project-id>.db`
- **Windows**: `%LOCALAPPDATA%\lytecache\<project-id>.db`

`<project-id>` is a short hash of your current working directory, so every
project on your machine automatically gets its own cache file -- two
different apps never collide, and nothing is left behind in your repo.

You can inspect or override this:

```python
LyteCache.default_path()     # -> Path, the resolved default location
cache.path                   # -> Path, this instance's actual file
cache.stats()["path"]        # the file is never a mystery
```

To pin the location explicitly (containers, tests, CI), either pass a path
or set an environment variable:

```python
cache = LyteCache("/data/cache.db")   # explicit escape hatch
```

```bash
export LYTECACHE_PATH=/data/cache.db  # takes priority over the default
```

## API

| Method | Description |
|---|---|
| `set(key, value, ttl=None)` | Store a value, optionally with a TTL in seconds. |
| `get(key, default=None, cls=None)` | Read a value; returns `default` on miss or expiry. Never raises on miss. `cls` reconstructs a dataclass/plain object from a stored JSON value. |
| `delete(*keys)` | Delete keys; returns the number actually deleted. |
| `exists(key)` | Whether a (non-expired) key is present. |
| `add(key, value, ttl=None)` | Set only if absent (atomic `SET NX`). |
| `replace(key, value, ttl=None)` | Set only if present (atomic `SET XX`). |
| `get_set(key, value)` | Atomically swap in a new value, returning the old one. |
| `set_many(mapping, ttl=None)` / `get_many(keys)` | Bulk set/get in a single transaction. |
| `expire(key, ttl)` / `persist(key)` | Set or remove a TTL on an existing key. |
| `ttl(key)` | Seconds remaining (`float`), `-1` if no expiry, `None` if missing. |
| `touch(key, ttl)` | Refresh a key's TTL (sliding expiration). |
| `incr(key, amount=1)` / `decr(key, amount=1)` | Atomic integer counters. |
| `incr_float(key, amount)` | Atomic float counter. |
| `keys(pattern="*")` | Lazily iterate matching keys (glob syntax). |
| `flush()` | Clear the current namespace. |
| `stats()` | Hits, misses, hit rate, key count, size, evictions, path. |
| `vacuum()` / `close()` | Reclaim disk space / shut down cleanly. |
| `memoize(ttl=None)` | Decorator that caches a function's return value. |
| `lock(name, timeout=30, blocking=True, poll=0.05)` | Process-safe context-manager lock. |

`LyteCache` also works as a context manager:

```python
with LyteCache() as cache:
    cache.set("k", "v")
```

## Serialization

`str`/`int`/`float`/`bytes`/`bool` round-trip exactly. Anything else --
`dict`, `list`, dataclasses, plain objects -- is JSON-encoded, and reads back
as a plain `dict`/`list` by default:

```python
from dataclasses import dataclass

@dataclass
class Address:
    city: str
    zip_code: str

@dataclass
class Person:
    name: str
    age: int
    address: Address

cache.set("p:1", Person("Samson", 30, Address("London", "E1")))

cache.get("p:1")               # {"name": "Samson", "age": 30, "address": {...}} -- plain dict
cache.get("p:1", cls=Person)   # Person(name="Samson", age=30, address=Address(...)) -- typed
```

`tuple` values are JSON-encoded too and come back as `list` -- there's no
tuple type in JSON.

Values that can't be represented as JSON raise `SerializationError` by
default (`serializer="auto"`). If you genuinely need to cache an arbitrary
Python object, opt into pickling explicitly:

```python
cache = LyteCache(serializer="pickle")   # falls back to pickle when JSON can't represent a value
```

**Security note:** unpickling can execute arbitrary code. The default
(`serializer="auto"`) and `serializer="json"` never write or read pickled
data, so this doesn't apply to them. Only `serializer="pickle"` (or
`serializer="auto"` with `allow_pickle=True`) can read pickled values --
treat a cache file that might contain pickled data like application code,
and never open one from an untrusted source.

## When to use lytecache

**Use it when:**
- You want caching, counters, or TTLs in a single-process (or single-machine,
  multi-process) application with no infrastructure to stand up.
- Scripts, CLIs, notebooks, small web services, background jobs.
- You want the cache file to survive restarts without running a separate
  daemon.

**Don't use it when:**
- You need a cache shared live across multiple servers/hosts -- SQLite is a
  local file, not a network service. Use Redis/Memcached instead.
- You have heavy concurrent write throughput from many processes -- SQLite's
  single-writer model will serialize writes and become a bottleneck.
- You need pub/sub, streams, or other Redis data structures beyond a
  key-value store with counters. `lytecache` intentionally stays small.

## Configuration reference

```python
LyteCache(
    path=None,             # explicit file path; default: LyteCache.default_path()
    namespace="default",   # logical partition within the database file
    max_keys=None,         # evict when the namespace exceeds this many keys
    max_bytes=None,        # evict when the namespace exceeds this many bytes
    eviction="lru",        # "lru" | "ttl" | "random" | "noeviction"
    sweep_interval=60.0,   # seconds between background maintenance passes;
                           # None disables the thread and does maintenance
                           # opportunistically every ~100 operations instead
    serializer="auto",     # "auto" | "json" (strict, no pickle) | "pickle"
    strict=False,          # True: raise on internal read errors instead of
                           # degrading to a miss
    allow_pickle=False,    # "auto" mode only: allow reading pickled values
                           # written by a serializer="pickle" cache
)
```

- **Eviction policies**: `lru` (default, evicts least-recently-used),
  `ttl` (evicts soonest-to-expire first), `random`, and `noeviction` (raises
  `CacheFullError` instead of evicting). `lfu` is a documented TODO.
- **Serialization**: see the [Serialization](#serialization) section above.
  `serializer="auto"` (the default) and `serializer="json"` never write or
  read pickled data; `serializer="pickle"` opts into pickling as a fallback
  for values JSON can't represent.
- **Concurrency**: safe across threads (one connection per thread) and
  across processes (SQLite WAL mode). `stats()` counters (hits/misses/etc.)
  are per-process, not shared cluster-wide.

See [SPEC.md](SPEC.md) for the on-disk schema and full semantics, and
[CHANGELOG.md](CHANGELOG.md) for release notes.

## License

Apache 2.0. See [LICENSE](LICENSE).
