# Changelog

All notable changes to this project are documented in this file.

## [0.1.0] - Unreleased

Initial release. Embedded, Redis-like caching backed by SQLite (via `pdo_sqlite`), matching the
storage format and semantics of the Python, Java, Node.js, and Go implementations in this
repository, plus a Laravel cache driver:

- Zero-config `new LyteCache()`, with the same default-path derivation as Python/Java/Node.js/Go.
- Core API: `set`/`get`/`delete`/`has`/`add`/`replace`/`getSet`/`setMany`/`getMany`.
- TTL/expiration: `expire`/`persist`/`ttl`/`touch`, lazy expiration plus opportunistic maintenance
  (`maintain()`, throttled by `sweepInterval` -- PHP has no background threads to run an active
  sweeper).
- Atomic counters (`incr`/`decr`/`incrFloat`) via a single-statement SQL UPSERT, correct across
  concurrent PHP-FPM worker processes sharing one file.
- Eviction policies: `Eviction::LRU` (default), `TTL`, `Random`, `NoEviction`.
- `keys()` generator (GLOB syntax), `flush()`, `stats()`, `vacuum()`, `close()`.
- `remember()` read-through helper, `Bytes` wrapper for binary values, typed rehydration via
  `get($key, class: SomeClass::class)`.
- `lock()`: process-safe distributed lock with `CacheLock::release()`/`block()`.
- Laravel integration: `LytecacheServiceProvider` (auto-discovered), `LytecacheStore` implementing
  `Illuminate\Contracts\Cache\Store` and `LockProvider`, `lytecache:maintain` artisan command,
  zero-config `cache.stores.lytecache` merge.

### Notes

- Session and queue drivers are `v0.2` candidates, not implemented in this release.
- `illuminate/contracts`/`illuminate/support` are `suggest`ed, not required -- the core has zero
  runtime dependencies beyond `ext-pdo`/`ext-pdo_sqlite`.
