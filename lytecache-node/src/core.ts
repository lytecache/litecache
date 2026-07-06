import Database from "better-sqlite3";
import type { Database as DatabaseType } from "better-sqlite3";
import { mkdirSync } from "node:fs";
import { dirname } from "node:path";

import "./dispose-polyfill.js";
import { CacheFullError, LyteCacheError, SchemaVersionError } from "./errors.js";
import { CacheLock, type LockOptions } from "./lock.js";
import { defaultPath as resolveDefaultPath } from "./paths.js";
import { DDL, PRAGMAS, SCHEMA_VERSION, TypeCode } from "./schema.js";
import {
  deserialize,
  serialize,
  type DeserializeOptions,
} from "./serialize.js";
import { sleepSyncMs } from "./sleep-sync.js";

export type Eviction = "lru" | "ttl" | "random" | "noeviction";

export interface LyteCacheOptions {
  /** Explicit database file path. Optional escape hatch; omit to use the zero-config default. */
  path?: string;
  /** Logical partition within the database file. Default `"default"`. */
  namespace?: string;
  /** Evict when the namespace exceeds this many keys. */
  maxKeys?: number;
  /** Evict when the namespace exceeds this many bytes. */
  maxBytes?: number;
  /** Eviction policy when `maxKeys`/`maxBytes` is exceeded. Default `"lru"`. */
  eviction?: Eviction;
  /** Seconds between background maintenance passes. `null` disables the timer and sweeps
   * opportunistically every ~100 operations instead. Default 60. */
  sweepInterval?: number | null;
  /** When true, internal read errors throw instead of degrading to a miss. Default false. */
  strict?: boolean;
  /** Called with a warning message when a non-strict read degrades to a miss. Default `console.warn`. */
  logger?: (message: string) => void;
}

export interface SetOptions {
  /** Time-to-live, in seconds. Omit (or `undefined`) for no expiry. */
  ttl?: number;
}

export type GetOptions = DeserializeOptions;

export interface CacheStatsSnapshot {
  hits: number;
  misses: number;
  hitRate: number;
  keyCount: number;
  sizeBytes: number;
  evictions: number;
  expiredRemoved: number;
  path: string;
}

const BATCH_DELETE_LIMIT = 500;
const LRU_FLUSH_THRESHOLD = 200;
const OPPORTUNISTIC_EVERY = 100;

interface RawRow {
  value: Buffer;
  value_type: number;
  expires_at: number | null;
}

function nowMs(): number {
  return Date.now();
}

/**
 * An embedded, Redis-like cache backed by a local SQLite file. The class is the entire public
 * API -- there are no module-level functions. Zero configuration is required:
 *
 * ```ts
 * import { LyteCache } from "lytecache";
 *
 * const cache = new LyteCache();
 * cache.set("user:42", { name: "Ada" }, { ttl: 300 });
 * cache.get("user:42"); // { name: "Ada" }
 * ```
 *
 * Every method is synchronous: better-sqlite3's native binding blocks the event loop for the
 * (microsecond-scale) duration of the query, which is what makes this API clean -- no `await`
 * noise, no connection pool, no partially-applied writes to reason about. See the README's "Why
 * synchronous?" section.
 */
export class LyteCache implements Disposable {
  private readonly db: DatabaseType;
  private readonly dbPath: string;
  private readonly namespace: string;
  private readonly maxKeys: number | null;
  private readonly maxBytes: number | null;
  private readonly eviction: Eviction;
  private readonly sweepIntervalSeconds: number | null;
  private readonly strict: boolean;
  private readonly logger: (message: string) => void;

  private hits = 0;
  private misses = 0;
  private evictionsCount = 0;
  private expiredRemovedCount = 0;
  private opCount = 0;
  private closed = false;

  private readonly lruBuffer = new Map<string, { lastAccessed: number; accessCount: number }>();
  private sweeperTimer: NodeJS.Timeout | null = null;

  constructor(options: LyteCacheOptions = {}) {
    this.dbPath = options.path ?? resolveDefaultPath();
    this.namespace = options.namespace ?? "default";
    this.maxKeys = options.maxKeys ?? null;
    this.maxBytes = options.maxBytes ?? null;
    this.eviction = options.eviction ?? "lru";
    this.sweepIntervalSeconds = options.sweepInterval === undefined ? 60 : options.sweepInterval;
    this.strict = options.strict ?? false;
    this.logger = options.logger ?? ((message: string) => console.warn(`lytecache: ${message}`));

    const parent = dirname(this.dbPath);
    mkdirSync(parent, { recursive: true });

    this.db = new Database(this.dbPath);
    this.initWithRetry();

    if (this.sweepIntervalSeconds !== null) {
      this.sweeperTimer = setInterval(() => this.sweepOnce(), this.sweepIntervalSeconds * 1000);
      this.sweeperTimer.unref();
    }
  }

  // -- construction / schema -------------------------------------------------

  /** Returns the resolved zero-config default database path. */
  static defaultPath(): string {
    return resolveDefaultPath();
  }

  /** The actual database file backing this instance. */
  get path(): string {
    return this.dbPath;
  }

  /**
   * Applies the PRAGMAs and schema, retrying on SQLITE_BUSY: when several processes create the
   * same fresh WAL-mode file for the first time simultaneously, the very first `journal_mode=WAL`
   * switch (which needs a brief exclusive lock) can collide before `busy_timeout` has had a
   * chance to matter, independent of how high `busy_timeout` itself is set. This is a well-known
   * SQLite cold-start race, not something a single PRAGMA setting resolves.
   */
  private initWithRetry(): void {
    const maxAttempts = 25;
    for (let attempt = 1; ; attempt++) {
      try {
        for (const pragma of PRAGMAS) {
          this.db.pragma(pragma.replace(/^PRAGMA\s+/i, ""));
        }
        this.initSchema();
        return;
      } catch (err) {
        const code = (err as { code?: string }).code;
        if (code === "SQLITE_BUSY" && attempt < maxAttempts) {
          sleepSyncMs(20);
          continue;
        }
        throw err;
      }
    }
  }

  private initSchema(): void {
    this.db.exec(DDL);
    const row = this.db.prepare("SELECT v FROM meta WHERE k = 'schema_version'").get() as
      | { v: string }
      | undefined;
    if (row === undefined) {
      // INSERT OR IGNORE: another process opening this same fresh file for the first time may
      // win this exact race between the SELECT above and this INSERT, in which case this becomes
      // a harmless no-op instead of a PRIMARY KEY constraint violation.
      this.db.prepare("INSERT OR IGNORE INTO meta (k, v) VALUES ('schema_version', '1')").run();
    } else {
      const version = Number.parseInt(row.v, 10);
      if (version > SCHEMA_VERSION) {
        throw new SchemaVersionError(
          `database schema version ${version} is newer than the version ${SCHEMA_VERSION} supported ` +
            "by this lytecache install; upgrade lytecache to open this file",
        );
      }
    }
  }

  private requireOpen(): void {
    if (this.closed) {
      throw new LyteCacheError("this LyteCache instance is closed");
    }
  }

  private hasCapacityLimits(): boolean {
    return this.maxKeys !== null || this.maxBytes !== null;
  }

  // -- key/value --------------------------------------------------------------

  /** Stores a value, optionally with a TTL in seconds. */
  set(key: string, value: unknown, options: SetOptions = {}): void {
    this.requireOpen();
    const { bytes, typeCode } = serialize(value);
    const now = nowMs();
    const expiresAt = options.ttl === undefined ? null : now + Math.round(options.ttl * 1000);

    if (this.eviction === "noeviction" && this.hasCapacityLimits()) {
      this.checkCapacityBeforeWrite(key);
    }

    this.db
      .prepare(
        `INSERT INTO cache
            (namespace, key, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
         VALUES (@ns, @key, @value, @vtype, @now, @expiresAt, @now, 0, @size)
         ON CONFLICT(namespace, key) DO UPDATE SET
            value = excluded.value,
            value_type = excluded.value_type,
            created_at = excluded.created_at,
            expires_at = excluded.expires_at,
            last_accessed = excluded.last_accessed,
            access_count = 0,
            size_bytes = excluded.size_bytes`,
      )
      .run({
        ns: this.namespace,
        key,
        value: bytes,
        vtype: typeCode,
        now,
        expiresAt,
        size: bytes.length,
      });

    this.maybeEvict();
    this.maybeOpportunisticMaintenance();
  }

  /**
   * Reads a value, or `defaultValue` (default `undefined`) on a miss or expiry. Never throws for
   * a missing key.
   */
  get<T = unknown>(key: string, defaultValue?: T, options?: GetOptions): T | undefined {
    this.requireOpen();
    const now = nowMs();
    let row: RawRow | undefined;
    try {
      row = this.db
        .prepare("SELECT value, value_type, expires_at FROM cache WHERE namespace = ? AND key = ?")
        .get(this.namespace, key) as RawRow | undefined;
    } catch (err) {
      if (this.strict) {
        throw new LyteCacheError(`get(${JSON.stringify(key)}) failed: ${String(err)}`, {
          cause: err,
        });
      }
      this.logger(`get(${JSON.stringify(key)}) failed, treating as a miss: ${String(err)}`);
      this.misses += 1;
      return defaultValue;
    }

    if (row === undefined) {
      this.misses += 1;
      this.maybeOpportunisticMaintenance();
      return defaultValue;
    }
    if (row.expires_at !== null && row.expires_at <= now) {
      this.misses += 1;
      this.deleteExpiredRow(key, now);
      this.maybeOpportunisticMaintenance();
      return defaultValue;
    }

    this.hits += 1;
    this.bufferLru(key, now);
    this.maybeOpportunisticMaintenance();

    try {
      return deserialize(row.value, row.value_type, options) as T;
    } catch (err) {
      if (this.strict) throw err;
      this.logger(`failed to deserialize key ${JSON.stringify(key)}: ${String(err)}`);
      return defaultValue;
    }
  }

  /** Deletes keys; returns the number actually deleted. */
  delete(...keys: string[]): number {
    this.requireOpen();
    if (keys.length === 0) return 0;
    const placeholders = keys.map(() => "?").join(",");
    const result = this.db
      .prepare(`DELETE FROM cache WHERE namespace = ? AND key IN (${placeholders})`)
      .run(this.namespace, ...keys);
    for (const key of keys) this.lruBuffer.delete(key);
    return result.changes;
  }

  /** Whether a (non-expired) key is present. */
  exists(key: string): boolean {
    this.requireOpen();
    const now = nowMs();
    const row = this.db
      .prepare(
        "SELECT 1 FROM cache WHERE namespace = ? AND key = ? AND (expires_at IS NULL OR expires_at > ?)",
      )
      .get(this.namespace, key, now);
    return row !== undefined;
  }

  /** Sets a value only if absent (atomic `SET NX`). Returns whether it was set. */
  add(key: string, value: unknown, options: SetOptions = {}): boolean {
    this.requireOpen();
    const { bytes, typeCode } = serialize(value);
    const now = nowMs();
    const expiresAt = options.ttl === undefined ? null : now + Math.round(options.ttl * 1000);

    if (this.eviction === "noeviction" && this.hasCapacityLimits()) {
      this.checkCapacityBeforeWrite(key);
    }

    // Single atomic UPSERT: the DO UPDATE branch only fires (and only "wins") when the existing
    // row is already expired, so a fresh key and an expired key both insert/overwrite in one
    // statement -- no separate check-then-write race between processes.
    const result = this.db
      .prepare(
        `INSERT INTO cache
            (namespace, key, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
         VALUES (@ns, @key, @value, @vtype, @now, @expiresAt, @now, 0, @size)
         ON CONFLICT(namespace, key) DO UPDATE SET
            value = excluded.value,
            value_type = excluded.value_type,
            created_at = excluded.created_at,
            expires_at = excluded.expires_at,
            last_accessed = excluded.last_accessed,
            access_count = 0,
            size_bytes = excluded.size_bytes
         WHERE cache.expires_at IS NOT NULL AND cache.expires_at <= @now`,
      )
      .run({
        ns: this.namespace,
        key,
        value: bytes,
        vtype: typeCode,
        now,
        expiresAt,
        size: bytes.length,
      });

    const won = result.changes === 1;
    if (won) this.maybeEvict();
    return won;
  }

  /** Sets a value only if present (atomic `SET XX`). Returns whether it was replaced. */
  replace(key: string, value: unknown, options: SetOptions = {}): boolean {
    this.requireOpen();
    const { bytes, typeCode } = serialize(value);
    const now = nowMs();
    const expiresAt = options.ttl === undefined ? null : now + Math.round(options.ttl * 1000);

    // A single UPDATE with the existence/expiry check in the WHERE clause is already atomic; no
    // separate check-then-write is needed.
    const result = this.db
      .prepare(
        `UPDATE cache SET value = @value, value_type = @vtype, created_at = @now, expires_at = @expiresAt,
            last_accessed = @now, access_count = 0, size_bytes = @size
         WHERE namespace = @ns AND key = @key AND (expires_at IS NULL OR expires_at > @now)`,
      )
      .run({
        ns: this.namespace,
        key,
        value: bytes,
        vtype: typeCode,
        now,
        expiresAt,
        size: bytes.length,
      });
    return result.changes > 0;
  }

  /** Atomically reads and replaces a value, returning the old one (or `undefined` if absent/expired). */
  getSet(key: string, value: unknown): unknown {
    this.requireOpen();
    const { bytes, typeCode } = serialize(value);
    const now = nowMs();

    const txn = this.db.transaction(() => {
      const row = this.db
        .prepare("SELECT value, value_type, expires_at FROM cache WHERE namespace = ? AND key = ?")
        .get(this.namespace, key) as RawRow | undefined;
      const oldValue =
        row !== undefined && (row.expires_at === null || row.expires_at > now)
          ? deserialize(row.value, row.value_type)
          : undefined;

      this.db
        .prepare(
          `INSERT INTO cache
              (namespace, key, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
           VALUES (@ns, @key, @value, @vtype, @now, NULL, @now, 0, @size)
           ON CONFLICT(namespace, key) DO UPDATE SET
              value = excluded.value,
              value_type = excluded.value_type,
              created_at = excluded.created_at,
              expires_at = NULL,
              last_accessed = excluded.last_accessed,
              access_count = 0,
              size_bytes = excluded.size_bytes`,
        )
        .run({ ns: this.namespace, key, value: bytes, vtype: typeCode, now, size: bytes.length });

      return oldValue;
    });
    return txn.immediate();
  }

  /** Sets every entry, in a single transaction. */
  setMany(entries: Record<string, unknown>, options: SetOptions = {}): void {
    this.requireOpen();
    const keys = Object.keys(entries);
    if (keys.length === 0) return;
    const now = nowMs();
    const expiresAt = options.ttl === undefined ? null : now + Math.round(options.ttl * 1000);

    const stmt = this.db.prepare(
      `INSERT INTO cache
          (namespace, key, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
       VALUES (@ns, @key, @value, @vtype, @now, @expiresAt, @now, 0, @size)
       ON CONFLICT(namespace, key) DO UPDATE SET
          value = excluded.value,
          value_type = excluded.value_type,
          created_at = excluded.created_at,
          expires_at = excluded.expires_at,
          last_accessed = excluded.last_accessed,
          access_count = 0,
          size_bytes = excluded.size_bytes`,
    );
    const txn = this.db.transaction((rows: string[]) => {
      for (const key of rows) {
        const { bytes, typeCode } = serialize(entries[key]);
        stmt.run({ ns: this.namespace, key, value: bytes, vtype: typeCode, now, expiresAt, size: bytes.length });
      }
    });
    txn.immediate(keys);
    this.maybeEvict();
  }

  /** Gets multiple values as a `Map`; missing/expired keys are simply absent from the result. */
  getMany(keys: string[]): Map<string, unknown> {
    this.requireOpen();
    const result = new Map<string, unknown>();
    if (keys.length === 0) return result;
    const now = nowMs();
    const stmt = this.db.prepare(
      "SELECT value, value_type, expires_at FROM cache WHERE namespace = ? AND key = ?",
    );
    let hits = 0;
    for (const key of keys) {
      const row = stmt.get(this.namespace, key) as RawRow | undefined;
      if (row === undefined) continue;
      if (row.expires_at !== null && row.expires_at <= now) continue;
      result.set(key, deserialize(row.value, row.value_type));
      this.bufferLru(key, now);
      hits += 1;
    }
    this.hits += hits;
    this.misses += keys.length - hits;
    return result;
  }

  // -- expiration -------------------------------------------------------------

  /** Sets (or overwrites) a key's TTL, in seconds. Returns whether the key exists (and isn't already expired). */
  expire(key: string, ttlSeconds: number): boolean {
    this.requireOpen();
    const now = nowMs();
    const expiresAt = now + Math.round(ttlSeconds * 1000);
    const result = this.db
      .prepare(
        "UPDATE cache SET expires_at = ? WHERE namespace = ? AND key = ? AND (expires_at IS NULL OR expires_at > ?)",
      )
      .run(expiresAt, this.namespace, key, now);
    return result.changes > 0;
  }

  /** Removes a key's TTL, if any. Returns whether the key existed with a TTL to remove. */
  persist(key: string): boolean {
    this.requireOpen();
    const now = nowMs();
    const result = this.db
      .prepare(
        "UPDATE cache SET expires_at = NULL WHERE namespace = ? AND key = ? AND expires_at IS NOT NULL AND expires_at > ?",
      )
      .run(this.namespace, key, now);
    return result.changes > 0;
  }

  /** Seconds remaining, `-1` if no expiry, `undefined` if the key doesn't exist (or already expired). */
  ttl(key: string): number | undefined {
    this.requireOpen();
    const now = nowMs();
    const row = this.db
      .prepare("SELECT expires_at FROM cache WHERE namespace = ? AND key = ?")
      .get(this.namespace, key) as { expires_at: number | null } | undefined;
    if (row === undefined) return undefined;
    if (row.expires_at === null) return -1;
    const remainingMs = row.expires_at - now;
    if (remainingMs <= 0) return undefined;
    return remainingMs / 1000;
  }

  /** Refreshes a key's TTL (sliding expiration). Equivalent to {@link expire}. */
  touch(key: string, ttlSeconds: number): boolean {
    return this.expire(key, ttlSeconds);
  }

  // -- atomic counters ----------------------------------------------------------

  /** Atomically adds `amount` (default 1) to an integer counter, creating it at 0 if absent. */
  incr(key: string, amount: number | bigint = 1): number | bigint {
    this.requireOpen();
    const text = this.atomicIncr(key, amount, TypeCode.INT, [TypeCode.INT]);
    return decodeIntText(text);
  }

  /** Atomically subtracts `amount` (default 1) from an integer counter. */
  decr(key: string, amount: number | bigint = 1): number | bigint {
    return this.incr(key, -amount);
  }

  /** Atomically adds `amount` to a float counter, creating it at 0 if absent. */
  incrFloat(key: string, amount: number): number {
    this.requireOpen();
    if (!Number.isFinite(amount)) {
      throw new TypeError("incrFloat amount must be a finite number");
    }
    const text = this.atomicIncr(key, amount, TypeCode.FLOAT, [TypeCode.INT, TypeCode.FLOAT]);
    return Number(text);
  }

  /**
   * Performs the atomic single-statement UPSERT that adds `amount` to the existing numeric value
   * (or 0 if absent/expired), mirroring the Python and Java implementations' SQL exactly so a
   * single SQLite statement -- not a JS-side read-modify-write -- is the unit of atomicity shared
   * across processes.
   */
  private atomicIncr(
    key: string,
    amount: number | bigint,
    resultType: number,
    allowedTypes: number[],
  ): string {
    const now = nowMs();
    const initialBytes = Buffer.from(amount.toString(), "utf8");
    const inClause = `(${allowedTypes.join(",")})`;
    // better-sqlite3 binds a plain JS `number` as SQLite REAL, never INTEGER -- so for an integer
    // result, `amount` must be bound as a BigInt (which binds as INTEGER); otherwise SQLite's
    // `CAST(value AS TEXT) + amount` promotes to REAL (e.g. producing "2.0" instead of "2"),
    // corrupting the decimal-text wire format this type code is supposed to hold.
    const boundAmount = resultType === TypeCode.INT ? BigInt(amount) : Number(amount);

    const sql = `
      INSERT INTO cache (namespace, key, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
      VALUES (@ns, @key, @blob, @rtype, @now, NULL, @now, 0, @size)
      ON CONFLICT(namespace, key) DO UPDATE SET
        value = CAST(CAST(
            (CASE WHEN cache.expires_at IS NOT NULL AND cache.expires_at <= @now THEN 0
                  ELSE CAST(cache.value AS TEXT) END) + @amount
            AS TEXT) AS BLOB),
        value_type = @rtype,
        expires_at = CASE
            WHEN cache.expires_at IS NOT NULL AND cache.expires_at <= @now THEN NULL
            ELSE cache.expires_at
        END,
        last_accessed = @now,
        size_bytes = LENGTH(CAST(
            (CASE WHEN cache.expires_at IS NOT NULL AND cache.expires_at <= @now THEN 0
                  ELSE CAST(cache.value AS TEXT) END) + @amount
            AS TEXT))
      WHERE (cache.expires_at IS NOT NULL AND cache.expires_at <= @now)
         OR cache.value_type IN ${inClause}
    `;

    const txn = this.db.transaction(() => {
      const result = this.db.prepare(sql).run({
        ns: this.namespace,
        key,
        blob: initialBytes,
        rtype: resultType,
        now,
        size: initialBytes.length,
        amount: boundAmount,
      });
      if (result.changes === 0) {
        throw new TypeError(
          `value for key ${JSON.stringify(key)} is not ${resultType === TypeCode.FLOAT ? "a number" : "an integer"}`,
        );
      }
      const row = this.db
        .prepare("SELECT value FROM cache WHERE namespace = ? AND key = ?")
        .get(this.namespace, key) as { value: Buffer } | undefined;
      if (row === undefined) {
        throw new LyteCacheError("unexpected: key not found after incr upsert");
      }
      return row.value.toString("utf8");
    });
    return txn.immediate();
  }

  // -- introspection & management -----------------------------------------------

  /** Lazily iterates keys matching a `GLOB` pattern (`*`, `?`, `[...]`), cursor-batched, never
   * loading the whole namespace at once. */
  *keys(pattern = "*"): IterableIterator<string> {
    this.requireOpen();
    const batchSize = 500;
    let lastKey = "";
    const now = nowMs();
    for (;;) {
      const rows = this.db
        .prepare(
          `SELECT key FROM cache
           WHERE namespace = ? AND key GLOB ? AND key > ?
             AND (expires_at IS NULL OR expires_at > ?)
           ORDER BY key LIMIT ?`,
        )
        .all(this.namespace, pattern, lastKey, now, batchSize) as { key: string }[];
      if (rows.length === 0) return;
      for (const row of rows) yield row.key;
      lastKey = rows[rows.length - 1]!.key;
      if (rows.length < batchSize) return;
    }
  }

  /** Clears every entry in the current namespace. */
  flush(): void {
    this.requireOpen();
    this.db.prepare("DELETE FROM cache WHERE namespace = ?").run(this.namespace);
    this.lruBuffer.clear();
  }

  /** A snapshot of cache statistics. */
  stats(): CacheStatsSnapshot {
    this.requireOpen();
    const row = this.db
      .prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(size_bytes), 0) as sz FROM cache WHERE namespace = ?")
      .get(this.namespace) as { cnt: number; sz: number };
    const total = this.hits + this.misses;
    return {
      hits: this.hits,
      misses: this.misses,
      hitRate: total === 0 ? 0 : this.hits / total,
      keyCount: row.cnt,
      sizeBytes: row.sz,
      evictions: this.evictionsCount,
      expiredRemoved: this.expiredRemovedCount,
      path: this.dbPath,
    };
  }

  /** Reclaims disk space (flushes buffered LRU metadata first). */
  vacuum(): void {
    this.requireOpen();
    this.flushLruBuffer();
    this.db.exec("VACUUM");
  }

  /** Shuts down cleanly: flushes buffered LRU metadata, stops the sweeper, and closes the database. */
  close(): void {
    if (this.closed) return;
    this.closed = true;
    if (this.sweeperTimer !== null) {
      clearInterval(this.sweeperTimer);
      this.sweeperTimer = null;
    }
    this.flushLruBuffer();
    this.db.close();
  }

  /** Enables `using cache = new LyteCache()` (TC39 explicit resource management). */
  [Symbol.dispose](): void {
    this.close();
  }

  // -- extras: memoize + lock -----------------------------------------------

  /** Read-through cache: returns the cached value if present, else computes it via `loader`, stores, and returns it. */
  memoize<T>(key: string, ttlSeconds: number | undefined, loader: () => T): T {
    const sentinel = Symbol("miss");
    const cached = this.get<T | symbol>(key, sentinel);
    if (cached !== sentinel) return cached as T;
    const computed = loader();
    this.set(key, computed, ttlSeconds === undefined ? {} : { ttl: ttlSeconds });
    return computed;
  }

  /** Async version of {@link memoize}, for a `loader` that returns a `Promise`. */
  async memoizeAsync<T>(
    key: string,
    ttlSeconds: number | undefined,
    loader: () => Promise<T>,
  ): Promise<T> {
    const sentinel = Symbol("miss");
    const cached = this.get<T | symbol>(key, sentinel);
    if (cached !== sentinel) return cached as T;
    const computed = await loader();
    this.set(key, computed, ttlSeconds === undefined ? {} : { ttl: ttlSeconds });
    return computed;
  }

  /** Wraps a function so repeated calls with the same arguments are memoized (key derived from the function's name and `JSON.stringify(args)`). */
  wrap<Args extends unknown[], R>(
    fn: (...args: Args) => R,
    options: { ttl?: number; keyPrefix?: string } = {},
  ): (...args: Args) => R {
    const prefix = options.keyPrefix ?? fn.name ?? "anonymous";
    return (...args: Args): R => {
      const key = `wrap:${prefix}:${JSON.stringify(args)}`;
      return this.memoize(key, options.ttl, () => fn(...args));
    };
  }

  /** Acquires a process-safe distributed lock. Use with `using` or call `.release()`/`.close()` when done. */
  lock(name: string, options?: LockOptions): CacheLock {
    this.requireOpen();
    return new CacheLock(this, name, options);
  }

  /** @internal Used by {@link CacheLock}; not part of the primary API. */
  tryAcquireLock(lockKey: string, token: string, ttlSeconds: number): boolean {
    return this.add(lockKey, token, { ttl: ttlSeconds });
  }

  /**
   * @internal Used by {@link CacheLock}; not part of the primary API. Atomically deletes the
   * lock only if it's still held by `token`, so a lock that already expired and was re-acquired
   * by someone else is never deleted out from under them.
   */
  releaseLock(lockKey: string, token: string): boolean {
    const txn = this.db.transaction(() => {
      const row = this.db
        .prepare("SELECT value FROM cache WHERE namespace = ? AND key = ?")
        .get(this.namespace, lockKey) as { value: Buffer } | undefined;
      if (row === undefined || row.value.toString("utf8") !== token) return false;
      const result = this.db
        .prepare("DELETE FROM cache WHERE namespace = ? AND key = ?")
        .run(this.namespace, lockKey);
      return result.changes > 0;
    });
    return txn.immediate();
  }

  // -- internal: LRU buffering, sweeping, eviction --------------------------

  private bufferLru(key: string, now: number): void {
    const entry = this.lruBuffer.get(key);
    if (entry === undefined) {
      this.lruBuffer.set(key, { lastAccessed: now, accessCount: 1 });
    } else {
      entry.lastAccessed = now;
      entry.accessCount += 1;
    }
    if (this.lruBuffer.size >= LRU_FLUSH_THRESHOLD) {
      this.flushLruBuffer();
    }
  }

  private flushLruBuffer(): void {
    if (this.lruBuffer.size === 0) return;
    const pending = [...this.lruBuffer.entries()];
    this.lruBuffer.clear();
    try {
      const stmt = this.db.prepare(
        "UPDATE cache SET last_accessed = ?, access_count = access_count + ? WHERE namespace = ? AND key = ?",
      );
      const txn = this.db.transaction((entries: [string, { lastAccessed: number; accessCount: number }][]) => {
        for (const [key, meta] of entries) {
          stmt.run(meta.lastAccessed, meta.accessCount, this.namespace, key);
        }
      });
      txn.immediate(pending);
    } catch (err) {
      if (this.strict) throw err;
      this.logger(`failed to flush LRU bookkeeping buffer: ${String(err)}`);
    }
  }

  private maybeOpportunisticMaintenance(): void {
    if (this.sweepIntervalSeconds !== null) return;
    this.opCount += 1;
    if (this.opCount >= OPPORTUNISTIC_EVERY) {
      this.opCount = 0;
      this.sweepOnce();
    }
  }

  private sweepOnce(): void {
    try {
      this.flushLruBuffer();
      const now = nowMs();
      // Bounded batch delete via subquery: plain SQLite doesn't support DELETE ... LIMIT, so the
      // batch is bounded by selecting the matching keys first.
      const result = this.db
        .prepare(
          `DELETE FROM cache WHERE namespace = ? AND key IN (
             SELECT key FROM cache WHERE namespace = ? AND expires_at IS NOT NULL AND expires_at <= ?
             LIMIT ?
           )`,
        )
        .run(this.namespace, this.namespace, now, BATCH_DELETE_LIMIT);
      this.expiredRemovedCount += result.changes;
      this.maybeEvict();
    } catch (err) {
      if (this.strict) throw err;
      this.logger(`sweep failed: ${String(err)}`);
    }
  }

  private deleteExpiredRow(key: string, now: number): void {
    try {
      this.db
        .prepare(
          "DELETE FROM cache WHERE namespace = ? AND key = ? AND expires_at IS NOT NULL AND expires_at <= ?",
        )
        .run(this.namespace, key, now);
    } catch (err) {
      this.logger(`failed to delete expired key ${JSON.stringify(key)}: ${String(err)}`);
    }
  }

  /**
   * Rejects a write outright if it would grow the dataset past `maxKeys`/`maxBytes` under
   * `"noeviction"` -- there's nothing to evict, so the write itself must fail rather than being
   * accepted and evicted after the fact. Updating an existing, non-expired key never grows the
   * dataset, so it's always allowed.
   */
  private checkCapacityBeforeWrite(key: string): void {
    const now = nowMs();
    const exists = this.db
      .prepare(
        "SELECT 1 FROM cache WHERE namespace = ? AND key = ? AND (expires_at IS NULL OR expires_at > ?)",
      )
      .get(this.namespace, key, now);
    if (exists !== undefined) return;
    const row = this.db
      .prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(size_bytes), 0) as sz FROM cache WHERE namespace = ?")
      .get(this.namespace) as { cnt: number; sz: number };
    if (this.maxKeys !== null && row.cnt >= this.maxKeys) {
      throw new CacheFullError(`cache is full (keys=${row.cnt}, max=${this.maxKeys})`);
    }
    if (this.maxBytes !== null && row.sz >= this.maxBytes) {
      throw new CacheFullError(`cache is full (bytes=${row.sz}, max=${this.maxBytes})`);
    }
  }

  private maybeEvict(): void {
    if (this.eviction === "noeviction") return;
    if (this.maxKeys === null && this.maxBytes === null) return;
    // Flush buffered last_accessed updates first so LRU eviction order reflects the access that
    // just happened, not a stale on-disk timestamp from before the read was buffered.
    if (this.eviction === "lru") {
      this.flushLruBuffer();
    }
    const orderBy =
      this.eviction === "lru"
        ? "last_accessed ASC"
        : this.eviction === "ttl"
          ? "(expires_at IS NULL) ASC, expires_at ASC"
          : "RANDOM()";
    for (;;) {
      const row = this.db
        .prepare("SELECT COUNT(*) as cnt, COALESCE(SUM(size_bytes), 0) as sz FROM cache WHERE namespace = ?")
        .get(this.namespace) as { cnt: number; sz: number };
      const overKeys = this.maxKeys !== null ? row.cnt - this.maxKeys : 0;
      const overBytes = this.maxBytes !== null && row.sz > this.maxBytes;
      if (overKeys <= 0 && !overBytes) return;
      const batch = Math.max(overKeys, 1);
      const result = this.db
        .prepare(
          `DELETE FROM cache WHERE namespace = ? AND key IN (
             SELECT key FROM cache WHERE namespace = ? ORDER BY ${orderBy} LIMIT ?
           )`,
        )
        .run(this.namespace, this.namespace, batch);
      if (result.changes === 0) return;
      this.evictionsCount += result.changes;
    }
  }
}

function decodeIntText(text: string): number | bigint {
  const big = BigInt(text);
  const MAX_SAFE = BigInt(Number.MAX_SAFE_INTEGER);
  const MIN_SAFE = BigInt(Number.MIN_SAFE_INTEGER);
  if (big > MAX_SAFE || big < MIN_SAFE) return big;
  return Number(big);
}
