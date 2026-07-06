/** The on-disk schema, treated as a public, versioned spec. See SPEC.md. */

export const SCHEMA_VERSION = 1;

export const PRAGMAS: readonly string[] = [
  // busy_timeout must be set first so that every subsequent statement on this connection --
  // including the journal_mode switch itself -- waits out contention from other
  // threads/processes instead of failing immediately.
  "PRAGMA busy_timeout = 5000",
  "PRAGMA journal_mode = WAL",
  "PRAGMA synchronous = NORMAL",
  "PRAGMA foreign_keys = ON",
];

export const DDL = `
CREATE TABLE IF NOT EXISTS cache (
  key            TEXT    NOT NULL,
  namespace      TEXT    NOT NULL DEFAULT 'default',
  value          BLOB    NOT NULL,
  value_type     INTEGER NOT NULL DEFAULT 0,
  created_at     INTEGER NOT NULL,
  expires_at     INTEGER,
  last_accessed  INTEGER NOT NULL,
  access_count   INTEGER NOT NULL DEFAULT 0,
  size_bytes     INTEGER NOT NULL,
  PRIMARY KEY (namespace, key)
) WITHOUT ROWID;

CREATE INDEX IF NOT EXISTS idx_cache_expires ON cache(expires_at) WHERE expires_at IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_cache_lru ON cache(namespace, last_accessed);

CREATE TABLE IF NOT EXISTS meta (
  k TEXT PRIMARY KEY,
  v TEXT NOT NULL
);
`;

/**
 * Value type codes -- see SPEC.md for the full table. 0-4 are cross-language portable; 5 is a
 * Python-only opt-in escape hatch; 6 is Java's native-serialization escape hatch. lytecache
 * never writes 5 or 6, and reading either raises {@link SerializationError} rather than
 * returning raw bytes silently.
 */
export const TypeCode = {
  BYTES: 0,
  STR: 1,
  INT: 2,
  FLOAT: 3,
  JSON: 4,
  PYTHON_PICKLE: 5,
  JAVA_SERIALIZED: 6,
} as const;

export type TypeCodeValue = (typeof TypeCode)[keyof typeof TypeCode];
