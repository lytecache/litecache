package lytecache

import "time"

// Memoize reads key from c; on a miss, it calls loader, stores the result
// (with ttl, if positive), and returns it. On a hit, it returns the cached
// value without calling loader.
//
// Go methods cannot have type parameters, so this read-through helper
// lives at package level rather than as a method on [*Cache].
//
// Unlike [TTL] on Set/Add/Replace, ttl <= 0 here means the memoized value
// never expires (there is no separate "no expiry" spelling available for a
// plain time.Duration parameter), rather than expiring immediately.
func Memoize[T any](c *Cache, key string, ttl time.Duration, loader func() (T, error)) (T, error) {
	var dest T
	found, err := c.Get(key, &dest)
	if err != nil {
		var zero T
		return zero, err
	}
	if found {
		return dest, nil
	}

	value, err := loader()
	if err != nil {
		var zero T
		return zero, err
	}

	var opts []SetOption
	if ttl > 0 {
		opts = append(opts, TTL(ttl))
	}
	if err := c.Set(key, value, opts...); err != nil {
		var zero T
		return zero, err
	}
	return value, nil
}
