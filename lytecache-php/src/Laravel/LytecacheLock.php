<?php

declare(strict_types=1);

namespace Lytecache\Laravel;

use Illuminate\Contracts\Cache\Lock as LockContract;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Lytecache\CacheLock;
use Lytecache\LyteCache;

/**
 * Adapts LyteCache's add()-based locking to Laravel's Lock contract.
 * Laravel's non-blocking get() and explicit-timeout block() have
 * different semantics from {@see LyteCache::lock()} (which always blocks
 * up to a timeout), so this talks to LyteCache::add()/releaseLock()
 * directly rather than wrapping {@see CacheLock}.
 */
final class LytecacheLock implements LockContract
{
    private readonly string $owner;

    public function __construct(
        private readonly LyteCache $cache,
        private readonly string $name,
        private readonly int $seconds,
        ?string $owner = null,
    ) {
        $this->owner = $owner ?? bin2hex(random_bytes(16));
    }

    public function get($callback = null): mixed
    {
        $acquired = $this->acquire();

        if ($callback === null) {
            return $acquired;
        }

        if (! $acquired) {
            return false;
        }

        try {
            return $callback();
        } finally {
            $this->release();
        }
    }

    public function block($seconds, $callback = null): mixed
    {
        $deadline = microtime(true) + $seconds;

        while (! $this->acquire()) {
            if (microtime(true) >= $deadline) {
                throw new LockTimeoutException("Unable to acquire lock [{$this->name}] in {$seconds} seconds.");
            }

            usleep(50_000);
        }

        if ($callback === null) {
            return true;
        }

        try {
            return $callback();
        } finally {
            $this->release();
        }
    }

    public function release(): bool
    {
        return $this->cache->releaseLock($this->name, $this->owner);
    }

    public function owner(): string
    {
        return $this->owner;
    }

    public function forceRelease(): void
    {
        $this->cache->delete(LyteCache::LOCK_KEY_PREFIX.$this->name);
    }

    private function acquire(): bool
    {
        return $this->cache->add(
            LyteCache::LOCK_KEY_PREFIX.$this->name,
            $this->owner,
            $this->seconds > 0 ? (float) $this->seconds : null
        );
    }
}
