package lytecache

import "errors"

// Sentinel errors returned by [Cache] methods. Use [errors.Is] to test for
// them; returned errors wrap these with additional context via fmt.Errorf's
// %w verb, so a direct equality check will not work.
var (
	// ErrCacheFull is returned by a write that would grow the namespace
	// beyond WithMaxKeys or WithMaxBytes while using the NoEviction policy.
	ErrCacheFull = errors.New("lytecache: cache is full")

	// ErrSerialization is returned when a value cannot be encoded for
	// storage (for example, a channel or a cyclic structure that
	// encoding/json cannot marshal), or when a stored value cannot be
	// decoded (for example, a value written by the Python or Java
	// implementations using their language-specific escape hatches).
	ErrSerialization = errors.New("lytecache: serialization error")

	// ErrSchemaVersion is returned when opening a database file whose
	// schema_version is newer than this library understands.
	ErrSchemaVersion = errors.New("lytecache: unsupported schema version")

	// ErrLockTimeout is returned by Lock when the lock could not be
	// acquired before the given timeout elapsed.
	ErrLockTimeout = errors.New("lytecache: lock acquisition timed out")

	// ErrNotNumeric is returned by Incr, Decr, and IncrFloat when the
	// existing value for a key is not a numeric type.
	ErrNotNumeric = errors.New("lytecache: existing value is not numeric")
)
