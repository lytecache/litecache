package lytecache

// schemaVersion is the current on-disk schema version. Opening a database
// file whose meta.schema_version is greater than this returns
// [ErrSchemaVersion].
const schemaVersion = 1

// ddl is byte-for-byte the same schema used by the Python, Java, and
// Node.js implementations -- see SPEC.md for the full cross-language
// storage contract.
const ddl = `
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

CREATE TABLE IF NOT EXISTS meta (k TEXT PRIMARY KEY, v TEXT NOT NULL);
`

// Type codes stored in cache.value_type. See SPEC.md.
const (
	typeBytes  = 0
	typeString = 1
	typeInt    = 2
	typeFloat  = 3
	typeJSON   = 4
	// typePythonPickle = 5 -- Python-only escape hatch, reading it errors.
	// typeJavaSerialized = 6 -- reserved, reading it errors.
)
