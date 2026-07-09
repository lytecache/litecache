# Changelog

All notable changes to this project are documented in this file.

## [0.2.0] - 2026-07-09

Initial release. Embedded, Redis-like caching backed by SQLite (via the pure-Go `modernc.org/sqlite` driver), matching the storage format and semantics of the Python, Java, Node.js, and PHP implementations in this repository:

- Zero-config `lytecache.New()`, with the same default-path derivation as Python/Java/Node.js.
- `Set`/`Get`/`Delete`/`Exists`/`Add`/`Replace`/`GetSet`/`SetMany`/`GetMany`, with typed `GetBytes`/`GetString`/`GetInt64`/`GetFloat64` convenience wrappers.
- TTL/expiration: `Expire`/`Persist`/`TTLOf`/`Touch`, lazy + active expiration.
- Atomic counters (`Incr`/`Decr`/`IncrFloat`) via a single-statement SQL UPSERT, safe under concurrent goroutines and concurrent OS processes.
- Eviction policies: `LRU` (default), `TTLPolicy`, `Random`, `NoEviction`.
- `Keys` cursor iterator (Go 1.23 `iter.Seq2`), `Flush`, `Stats`, `Vacuum`, `Close` (idempotent).
- `Memoize` read-through helper (package-level generic function).
- `Lock`: process-safe distributed lock.
- Sentinel errors (`ErrCacheFull`, `ErrSerialization`, `ErrSchemaVersion`, `ErrLockTimeout`, `ErrNotNumeric`), all matchable via `errors.Is`.
- Runnable `Example` functions for every major feature, rendered on pkg.go.dev.
