package lytecache

import (
	"database/sql"
	"errors"
	"fmt"
	"time"
)

// RowInfo is raw, on-disk metadata for a single key, returned by
// [Cache.Inspect]. It exposes the same bookkeeping the storage engine
// itself tracks, primarily for debugging and introspection tools (see the
// lytecache CLI's `dump` and `keys --long` commands) -- ordinary
// applications should use Get/GetMany instead, which decode the value
// itself rather than describing it.
type RowInfo struct {
	// ValueType is the raw value_type code (0-6) as stored on disk. See
	// SPEC.md for what each code means.
	ValueType int
	// SizeBytes is the stored value's size on disk.
	SizeBytes int64
	// CreatedAt is when the key was first written.
	CreatedAt time.Time
	// ExpiresAt is nil if the key has no TTL.
	ExpiresAt *time.Time
	// LastAccessed is the most recent read or write, used for LRU eviction
	// ordering. It reflects buffered-but-not-yet-flushed reads only after
	// they are flushed (see the package doc on LRU buffering).
	LastAccessed time.Time
	// AccessCount is the number of reads since the key was last written.
	AccessCount int64
}

// Inspect returns raw on-disk metadata for key, without decoding its value.
// found is false if the key does not exist or has already expired.
func (c *Cache) Inspect(key string) (info RowInfo, found bool, err error) {
	const q = `
SELECT value_type, size_bytes, created_at, expires_at, last_accessed, access_count
FROM cache WHERE namespace = ? AND key = ?`
	var createdAt, lastAccessed int64
	var expiresAt sql.NullInt64
	scanErr := c.readDB.QueryRow(q, c.namespace, key).
		Scan(&info.ValueType, &info.SizeBytes, &createdAt, &expiresAt, &lastAccessed, &info.AccessCount)
	switch {
	case errors.Is(scanErr, sql.ErrNoRows):
		return RowInfo{}, false, nil
	case scanErr != nil:
		return RowInfo{}, false, fmt.Errorf("lytecache: inspect %q: %w", key, scanErr)
	}

	if expiresAt.Valid && expiresAt.Int64 <= nowMillis() {
		c.deleteRaw(key)
		return RowInfo{}, false, nil
	}

	info.CreatedAt = time.UnixMilli(createdAt)
	info.LastAccessed = time.UnixMilli(lastAccessed)
	if expiresAt.Valid {
		t := time.UnixMilli(expiresAt.Int64)
		info.ExpiresAt = &t
	}
	return info, true, nil
}
