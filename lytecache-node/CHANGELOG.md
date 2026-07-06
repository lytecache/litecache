# Changelog

All notable changes to this project are documented in this file.

## [0.1.0] - Unreleased

Initial release. Embedded, Redis-like caching backed by SQLite (via better-sqlite3), matching the
storage format and semantics of the Python and Java implementations in this repository:

- Zero-config `new LyteCache()`, with the same default-path derivation as Python/Java.
- Synchronous API: `set`/`get`/`delete`/`exists`/`add`/`replace`/`getSet`/`setMany`/`getMany`.
- TTL/expiration: `expire`/`persist`/`ttl`/`touch`, lazy + active expiration.
- Atomic counters (`incr`/`decr`/`incrFloat`) via single-statement SQL UPSERT, with `bigint`
  support beyond `Number.MAX_SAFE_INTEGER`.
- Eviction policies: `lru` (default), `ttl`, `random`, `noeviction`.
- `keys()` cursor iterator, `flush()`, `stats()`, `vacuum()`, `close()`.
- `memoize`/`memoizeAsync`/`wrap` read-through helpers.
- `lock()`: process-safe distributed lock with `Symbol.dispose` support.
- Dual ESM + CJS build with type declarations.
