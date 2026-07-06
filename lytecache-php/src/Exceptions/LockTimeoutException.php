<?php

declare(strict_types=1);

namespace Lytecache\Exceptions;

use Lytecache\LyteCache;

/**
 * Thrown by {@see LyteCache::lock()} when the lock could not be
 * acquired before the given timeout elapsed.
 */
class LockTimeoutException extends LytecacheException {}
