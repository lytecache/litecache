<?php

declare(strict_types=1);

namespace Lytecache\Exceptions;

use Lytecache\Eviction;

/**
 * Thrown by a write that would grow the namespace beyond maxKeys/maxBytes
 * while using {@see Eviction::NoEviction}.
 */
class CacheFullException extends LytecacheException {}
