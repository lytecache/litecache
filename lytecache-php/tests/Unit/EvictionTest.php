<?php

declare(strict_types=1);

namespace Lytecache\Tests\Unit;

use Lytecache\Eviction;
use Lytecache\Exceptions\CacheFullException;
use Lytecache\Tests\TestCase;

final class EvictionTest extends TestCase
{
    public function test_lru_evicts_least_recently_used_first(): void
    {
        $cache = $this->newCache(maxKeys: 3, eviction: Eviction::LRU);

        // last_accessed has millisecond resolution (matching the
        // cross-language wire format), so operations need to be spaced
        // out to get a deterministic order.
        $tick = static fn () => usleep(5_000);

        $cache->set('a', '1');
        $tick();
        $cache->set('b', '2');
        $tick();
        $cache->set('c', '3');
        $tick();

        $cache->get('a'); // touch "a" so it's no longer least-recently-used
        $tick();

        $cache->set('d', '4');

        self::assertTrue($cache->has('a'), 'recently-touched key should survive');
        self::assertFalse($cache->has('b'), 'least-recently-used key should be evicted');
        self::assertTrue($cache->has('c'));
        self::assertTrue($cache->has('d'));
    }

    public function test_ttl_policy_evicts_soonest_to_expire_first(): void
    {
        $cache = $this->newCache(maxKeys: 2, eviction: Eviction::TTL);

        $cache->set('soon', '1', ttl: 600.0);
        $cache->set('later', '2', ttl: 3600.0);
        $cache->set('new', '3');

        self::assertFalse($cache->has('soon'));
        self::assertTrue($cache->has('later'));
        self::assertTrue($cache->has('new'));
    }

    public function test_no_eviction_rejects_new_key_past_limit(): void
    {
        $cache = $this->newCache(maxKeys: 2, eviction: Eviction::NoEviction);

        $cache->set('a', '1');
        $cache->set('b', '2');

        $this->expectException(CacheFullException::class);
        $cache->set('c', '3');
    }

    public function test_no_eviction_allows_updating_existing_key_at_limit(): void
    {
        $cache = $this->newCache(maxKeys: 2, eviction: Eviction::NoEviction);

        $cache->set('a', '1');
        $cache->set('b', '2');
        $cache->set('a', 'updated'); // should not throw

        self::assertSame('updated', $cache->get('a'));
    }

    public function test_random_eviction_keeps_within_limit(): void
    {
        $cache = $this->newCache(maxKeys: 3, eviction: Eviction::Random);

        for ($i = 0; $i < 10; $i++) {
            $cache->set(chr(97 + $i), $i);
        }

        $stats = $cache->stats();
        self::assertLessThanOrEqual(3, $stats->keyCount);
        self::assertGreaterThan(0, $stats->evictions);
    }

    public function test_max_bytes_eviction(): void
    {
        $cache = $this->newCache(maxBytes: 20, eviction: Eviction::LRU);

        for ($i = 0; $i < 10; $i++) {
            $cache->set(chr(97 + $i), '0123456789');
        }

        $stats = $cache->stats();
        self::assertGreaterThan(0, $stats->evictions);
    }
}
