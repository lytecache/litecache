<?php

declare(strict_types=1);

namespace Lytecache;

/**
 * A process-safe lock obtained from {@see LyteCache::lock()}. Already held
 * by the time it is returned to you; call release() when done, or use
 * block() to run a callback and release automatically (even if it throws).
 */
final class CacheLock
{
    private bool $released = false;

    public function __construct(
        private readonly LyteCache $cache,
        private readonly string $name,
        private readonly string $token,
    ) {}

    /**
     * Releases the lock. Only removes the underlying row if this lock's
     * token still matches what is stored -- guarding against releasing a
     * lock that expired and was subsequently acquired by someone else.
     * Safe to call more than once.
     */
    public function release(): bool
    {
        if ($this->released) {
            return true;
        }

        $this->released = true;

        return $this->cache->releaseLock($this->name, $this->token);
    }

    /**
     * Runs $callback while holding the lock, releasing it afterward
     * whether or not $callback throws.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @return T
     */
    public function block(callable $callback): mixed
    {
        try {
            return $callback();
        } finally {
            $this->release();
        }
    }

    public function __destruct()
    {
        $this->release();
    }
}
