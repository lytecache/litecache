"""lytecache: Redis-like caching with zero infrastructure, backed by SQLite."""

from .core import LyteCache
from .exceptions import (
    CacheFullError,
    LockTimeout,
    LyteCacheError,
    SchemaVersionError,
    SerializationError,
)

__version__ = "0.2.0"

__all__ = [
    "LyteCache",
    "LyteCacheError",
    "CacheFullError",
    "SerializationError",
    "SchemaVersionError",
    "LockTimeout",
    "__version__",
]
