<?php

declare(strict_types=1);

namespace Lytecache\Laravel;

use Illuminate\Contracts\Cache\Lock as LockContract;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Store;
use Lytecache\LyteCache;

/**
 * Adapts {@see LyteCache} to Laravel's cache Store (and LockProvider)
 * contracts, so it can be used as any other Cache::store() driver -- the
 * standard Cache facade, @cache patterns, Cache::lock(), the rate
 * limiter, etc. -- with no other code changes.
 */
final class LytecacheStore implements LockProvider, Store
{
    public function __construct(
        private readonly LyteCache $cache,
        private readonly string $prefix = '',
    ) {}

    public function get($key): mixed
    {
        return $this->cache->get($this->prefixed($key));
    }

    /**
     * @param  string[]  $keys
     * @return array<string, mixed>
     */
    public function many(array $keys): array
    {
        $prefixedToOriginal = [];
        foreach ($keys as $key) {
            $prefixedToOriginal[$this->prefixed($key)] = $key;
        }

        $found = $this->cache->getMany(array_keys($prefixedToOriginal));

        $result = [];
        foreach ($prefixedToOriginal as $prefixedKey => $originalKey) {
            $result[$originalKey] = $found[$prefixedKey] ?? null;
        }

        return $result;
    }

    public function put($key, $value, $seconds): bool
    {
        $this->cache->set($this->prefixed($key), $value, $seconds > 0 ? (float) $seconds : null);

        return true;
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public function putMany(array $values, $seconds): bool
    {
        $prefixed = [];
        foreach ($values as $key => $value) {
            $prefixed[$this->prefixed((string) $key)] = $value;
        }

        $this->cache->setMany($prefixed, $seconds > 0 ? (float) $seconds : null);

        return true;
    }

    public function increment($key, $value = 1): int
    {
        return $this->cache->incr($this->prefixed($key), (int) $value);
    }

    public function decrement($key, $value = 1): int
    {
        return $this->cache->decr($this->prefixed($key), (int) $value);
    }

    public function forever($key, $value): bool
    {
        $this->cache->set($this->prefixed($key), $value, null);

        return true;
    }

    public function touch($key, $seconds): bool
    {
        return $this->cache->expire($this->prefixed($key), (float) $seconds);
    }

    public function forget($key): bool
    {
        return $this->cache->delete($this->prefixed($key)) > 0;
    }

    public function flush(): bool
    {
        $this->cache->flush();

        return true;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function lock($name, $seconds = 0, $owner = null): LockContract
    {
        return new LytecacheLock($this->cache, $this->prefixed($name), (int) $seconds, $owner);
    }

    public function restoreLock($name, $owner): LockContract
    {
        return $this->lock($name, 0, $owner);
    }

    private function prefixed(string $key): string
    {
        return $this->prefix === '' ? $key : $this->prefix.$key;
    }
}
