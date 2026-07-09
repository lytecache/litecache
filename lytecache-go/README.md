# lytecache

Redis-like caching for Go with zero infrastructure -- no server, no CGO, just a file. `lytecache` gives you the familiar Redis API surface -- `Set`/`Get`, TTLs, atomic counters, eviction, distributed locks -- backed by a local SQLite file instead of a daemon, using a pure-Go SQLite driver so `go build`/`go install`/cross-compilation stay exactly as simple as they'd be without it.

## Install

```bash
go get github.com/lytecache/lytecache-go
```

## Quickstart

```go
import "github.com/lytecache/lytecache-go"

cache, err := lytecache.New() // no path, no setup -- just works
defer cache.Close()

cache.Set("user:42", map[string]any{"name": "Ada"}, lytecache.TTL(5*time.Minute))

var user map[string]any
found, err := cache.Get("user:42", &user)

n, err := cache.Incr("hits", 1)
```

That's it. The first call to `lytecache.New()` creates the database file (including any missing parent directories) and applies the schema automatically. There's no `Init()`, no migration step, and no server to start.

## API

| Method | Description |
|---|---|
| `Set(key, value, opts...)` | Store a value. `TTL(d)` is the optional expiry. |
| `Get(key, dest)` | Read into `dest` (a pointer); `found=false` on miss or expiry. Never errors on a miss. |
| `GetBytes`/`GetString`/`GetInt64`/`GetFloat64(key)` | Typed convenience wrappers around `Get`. |
| `Delete(keys...)` | Delete keys; returns how many actually existed. |
| `Exists(key)` | Whether a (non-expired) key is present. |
| `Add(key, value, opts...)` | Set only if absent (atomic `SET NX`). |
| `Replace(key, value, opts...)` | Set only if present (atomic `SET XX`). |
| `GetSet(key, value, dest)` | Atomically swap in a new value, decoding the old one into `dest`. |
| `SetMany(entries, opts...)` / `GetMany(keys)` | Bulk set/get in a single transaction; `GetMany` returns `map[string]RawValue` (`.Decode(dest)` per value). |
| `Expire(key, ttl)` / `Persist(key)` | Set or remove a TTL on an existing key. |
| `TTLOf(key)` | Remaining TTL, whether it has one, and whether the key exists. |
| `Touch(key, ttl)` | Refresh a key's TTL (sliding expiration). |
| `Incr(key, amount)` / `Decr(key, amount)` | Atomic integer counters. |
| `IncrFloat(key, amount)` | Atomic float counter. |
| `Keys(pattern)` | Lazily iterate matching keys (`iter.Seq2[string, error]`, GLOB syntax: `*`, `?`, `[...]`). |
| `Flush()` | Clear the current namespace. |
| `Stats()` | Hits, misses, hit rate, key count, size, evictions, path. |
| `Vacuum()` / `Close()` | Reclaim disk space / shut down cleanly (idempotent). |
| `lytecache.Memoize(cache, key, ttl, loader)` | Read-through cache for a computed value. Package-level generic function -- Go methods can't have type parameters. |
| `Lock(name, timeout)` | Process-safe distributed lock; release via `lock.Release()`. |

All errors that indicate a specific condition are sentinel errors you can test with `errors.Is`: `ErrCacheFull`, `ErrSerialization`, `ErrSchemaVersion`, `ErrLockTimeout`, `ErrNotNumeric`.

## Why a pure-Go SQLite driver?

`lytecache` uses [`modernc.org/sqlite`](https://pkg.go.dev/modernc.org/sqlite), a CGO-free, pure-Go port of SQLite, instead of a cgo-based binding. This is a deliberate choice, not an implementation detail:

- **No C toolchain required.** `go build`/`go install`/`go get` work the same everywhere, with no compiler, no `CGO_ENABLED=1`, and no platform-specific build flags.
- **Cross-compilation stays trivial.** Building a Linux binary from macOS (or any other combination) just works, exactly like any other pure-Go dependency.
- **It fits the zero-friction brand.** The whole point of `lytecache` is "no infrastructure" -- that shouldn't stop at "no server" and quietly require a working C build environment.

## When to use lytecache

**Good fit:**
- Single-node Go services (or single-machine, multi-process deployments) that want caching, counters, or TTLs with zero infrastructure.
- CLIs, scripts, small services, background jobs, test fixtures.
- A cache that survives process restarts without running a separate daemon.
- Multi-process coordination via the process-safe distributed lock.
- Mixed-language systems where a Go process needs to share a cache file with a Python, Java, or Node.js one.

**Not a good fit:**
- A cache shared live across multiple servers/hosts -- SQLite is a local file, not a network service. Use Redis/Memcached.
- Heavy concurrent write throughput from many processes -- SQLite's single-writer model will serialize writes and become a bottleneck.
- Pub/sub, streams, or other Redis data structures beyond key-value + counters -- lytecache intentionally stays small.

## Where is my data?

By default, `lytecache.New()` stores its file at:

```
<platform cache dir>/lytecache/<project-id>.db
```

resolved via [`os.UserCacheDir`](https://pkg.go.dev/os#UserCacheDir), which already implements the XDG Base Directory spec on Linux, `~/Library/Caches` on macOS, and `%LocalAppData%` on Windows.

`<project-id>` is the first 12 hex characters of the SHA-256 hash of your current working directory's resolved, absolute path -- identical to the Python, Java, and Node.js implementations' derivation, so every project gets its own file automatically, and a Go process and a Python/Java/Node.js process started from the same directory share one cache.

```go
lytecache.DefaultPath() // (string, error) -- the resolved default location
cache.Path()            // string -- this instance's actual file
```

Override it explicitly:

```go
cache, err := lytecache.New(lytecache.WithPath("/data/cache.db")) // explicit escape hatch
```

```bash
export LYTECACHE_PATH=/data/cache.db  # takes priority over the default
```

## Configuration reference

```go
cache, err := lytecache.New(
    lytecache.WithPath("/data/cache.db"),          // optional; default: lytecache.DefaultPath()
    lytecache.WithNamespace("sessions"),            // logical partition within the database file
    lytecache.WithMaxKeys(100_000),                 // evict when the namespace exceeds this many keys
    lytecache.WithMaxBytes(256<<20),                // evict when the namespace exceeds this many bytes
    lytecache.WithEviction(lytecache.LRU),          // LRU (default), TTLPolicy, Random, NoEviction
    lytecache.WithSweepInterval(60*time.Second),    // 0 disables the goroutine -> opportunistic mode
    lytecache.WithStrict(false),                    // true: return internal read errors instead of a miss
    lytecache.WithLogger(slog.Default()),           // used for non-strict-mode warnings
)
```

- **Eviction policies**: `LRU` (default, evicts least-recently-used), `TTLPolicy` (soonest-to-expire first), `Random`, and `NoEviction` (returns an error wrapping `ErrCacheFull` instead of evicting). LFU is a documented `v0.2` consideration, not yet implemented.
- **Concurrency**: a `*Cache` is safe for concurrent use by multiple goroutines, like `*http.Client`, and safe for concurrent use by multiple OS processes sharing one file.
- **Not yet included** (see `SPEC.md` and the package doc for the full list of deliberate exclusions): networking, pub/sub, clustering, `context.Context` plumbing on every method. These are `v0.2`-or-later considerations, not oversights.

See [SPEC.md](SPEC.md) for the on-disk schema, type codes, and full cross-language semantics, and the package documentation on [pkg.go.dev](https://pkg.go.dev/github.com/lytecache/lytecache-go) for runnable examples of every major feature.

## CLI

Looking for a command-line tool to inspect and manipulate lytecache database files from a shell (like `redis-cli`, but for a file)? See [lytecache-cli](https://github.com/lytecache/lytecache-cli) -- a separate repo/module that depends on this library's public API rather than living inside it, so this module stays a pure library with no CLI-only dependencies (`cobra`, `readline`) pulled into consumers who only want `import "github.com/lytecache/lytecache-go"`.

## License

Apache License 2.0. See [LICENSE](LICENSE).
