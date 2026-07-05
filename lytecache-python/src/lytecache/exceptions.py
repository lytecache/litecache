"""Exception hierarchy for lytecache."""

from __future__ import annotations


class LyteCacheError(Exception):
    """Base class for all exceptions raised by lytecache."""


class CacheFullError(LyteCacheError):
    """Raised when the cache is full and the eviction policy is ``noeviction``."""


class SerializationError(LyteCacheError):
    """Raised when a value cannot be serialized to, or deserialized from, storage."""


class SchemaVersionError(LyteCacheError):
    """Raised when a database file's schema version is newer than this library supports."""


class LockTimeout(LyteCacheError):
    """Raised by :meth:`LyteCache.lock` when the lock cannot be acquired in time."""
