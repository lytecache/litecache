# Changelog

All notable changes to this project are documented in this file.

## [0.2.0] - 2026-07-07

Initial release. Embedded, Redis-like caching backed by SQLite (via `org.xerial:sqlite-jdbc`),
matching the storage format and semantics of the Python, Node.js, Go, and PHP implementations in
this repository:

- Zero-config `new LyteCache()` / `LyteCache.builder()`, with the same default-path derivation as
  the other implementations.
- Core API: `set`/`get`/`delete`/`exists`/`add`/`replace`/`getSet`/`setAll`/`getAll`, typed getters
  (`getString`/`getLong`/`getDouble`/`getBytes`), and generic `get(key, Class<T>)` /
  `get(key, TypeReference<T>)` for POJOs, records, and generic types a raw `Class` can't express.
- TTL/expiration: `expire`/`persist`/`ttl`/`touch` using `Duration` (no unit ambiguity), lazy
  expiration on every read, plus a background sweeper thread for active cleanup.
- Atomic counters (`incr`/`decr`/`incrDouble`) via a single-statement SQL UPSERT, thread-safe and
  safe across processes sharing one file.
- Eviction policies: `LRU` (default), `TTL`, `RANDOM`, `NOEVICTION`.
- `keys()` (lazy `Stream<String>`, GLOB syntax), `flush()`, `stats()`, `vacuum()`, `close()`
  (`AutoCloseable`).
- `memoize()` read-through helper.
- `lock()`: process-safe distributed lock, `AutoCloseable` via try-with-resources.
- Jackson-based serialization: values are portable JSON, readable by every other language's
  implementation; native Java serialization is never used for the persisted format.
