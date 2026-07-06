package lytecache

import (
	"database/sql"
	"errors"
	"fmt"
	"time"
)

// Expire sets or overwrites the TTL on an existing key, reporting whether
// the key existed (and was not already expired). Zero or negative ttl
// expires the key immediately.
func (c *Cache) Expire(key string, ttl time.Duration) (bool, error) {
	now := nowMillis()
	expiresAt := now + ttl.Milliseconds()
	const q = `
UPDATE cache SET expires_at = ?
WHERE namespace = ? AND key = ? AND (expires_at IS NULL OR expires_at > ?)`
	res, err := c.writeDB.Exec(q, expiresAt, c.namespace, key, now)
	if err != nil {
		return false, fmt.Errorf("lytecache: expire %q: %w", key, err)
	}
	n, err := res.RowsAffected()
	if err != nil {
		return false, fmt.Errorf("lytecache: expire %q: %w", key, err)
	}
	return n > 0, nil
}

// Persist removes any TTL from an existing key, reporting whether the key
// existed (and was not already expired).
func (c *Cache) Persist(key string) (bool, error) {
	now := nowMillis()
	const q = `
UPDATE cache SET expires_at = NULL
WHERE namespace = ? AND key = ? AND (expires_at IS NULL OR expires_at > ?)`
	res, err := c.writeDB.Exec(q, c.namespace, key, now)
	if err != nil {
		return false, fmt.Errorf("lytecache: persist %q: %w", key, err)
	}
	n, err := res.RowsAffected()
	if err != nil {
		return false, fmt.Errorf("lytecache: persist %q: %w", key, err)
	}
	return n > 0, nil
}

// Touch refreshes an existing key's TTL to ttl (sliding expiration),
// reporting whether the key existed (and was not already expired). It is
// equivalent to Expire, provided as a more descriptive name for the
// sliding-expiration use case.
func (c *Cache) Touch(key string, ttl time.Duration) (bool, error) {
	return c.Expire(key, ttl)
}

// TTLOf reports the remaining time-to-live for key.
//
//   - found=false means the key does not exist (or is already expired).
//   - found=true, hasExpiry=false means the key exists with no TTL; ttl is
//     meaningless in that case.
//   - found=true, hasExpiry=true gives the remaining ttl (never negative;
//     an expired-but-not-yet-swept row reports as not found instead).
func (c *Cache) TTLOf(key string) (ttl time.Duration, hasExpiry bool, found bool, err error) {
	const q = `SELECT expires_at FROM cache WHERE namespace = ? AND key = ?`
	var expiresAt sql.NullInt64
	scanErr := c.readDB.QueryRow(q, c.namespace, key).Scan(&expiresAt)
	switch {
	case errors.Is(scanErr, sql.ErrNoRows):
		return 0, false, false, nil
	case scanErr != nil:
		wrapped := fmt.Errorf("lytecache: ttlof %q: %w", key, scanErr)
		if c.strict {
			return 0, false, false, wrapped
		}
		c.logger.Warn("lytecache: read error, degrading to miss", "key", key, "error", wrapped)
		return 0, false, false, nil
	}

	if !expiresAt.Valid {
		return 0, false, true, nil
	}

	now := nowMillis()
	if expiresAt.Int64 <= now {
		c.deleteRaw(key)
		return 0, false, false, nil
	}
	return time.Duration(expiresAt.Int64-now) * time.Millisecond, true, true, nil
}
