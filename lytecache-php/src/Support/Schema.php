<?php

declare(strict_types=1);

namespace Lytecache\Support;

/**
 * The on-disk schema, byte-for-byte identical to the Python, Java, Node.js,
 * and Go implementations -- see SPEC.md for the full cross-language
 * storage contract.
 */
final class Schema
{
    public const VERSION = 1;

    public const TYPE_BYTES = 0;

    public const TYPE_STRING = 1;

    public const TYPE_INT = 2;

    public const TYPE_FLOAT = 3;

    public const TYPE_JSON = 4;

    public const TYPE_PYTHON_PICKLE = 5;

    public const TYPE_JAVA_SERIALIZED = 6;

    public const DDL = <<<'SQL'
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
        SQL;

    /**
     * Applied in order on every new connection.
     */
    public const PRAGMAS = [
        'PRAGMA busy_timeout = 5000',
        'PRAGMA journal_mode = WAL',
        'PRAGMA synchronous = NORMAL',
        'PRAGMA foreign_keys = ON',
    ];
}
