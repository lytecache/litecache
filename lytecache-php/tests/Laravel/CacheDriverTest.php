<?php

declare(strict_types=1);

namespace Lytecache\Tests\Laravel;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Lytecache\LyteCache;

final class CacheDriverTest extends TestCase
{
    public function test_driver_is_registered_via_auto_discovery(): void
    {
        $store = Cache::store('lytecache');
        self::assertNotNull($store);
    }

    public function test_default_store_is_lytecache(): void
    {
        Cache::put('k', 'v');
        self::assertSame('v', Cache::get('k'));
    }

    public function test_get_put_remember(): void
    {
        $store = Cache::store('lytecache');

        self::assertNull($store->get('missing'));

        $store->put('k', ['name' => 'Ada'], 60);
        self::assertSame(['name' => 'Ada'], $store->get('k'));

        $calls = 0;
        $value = $store->remember('computed', 60, function () use (&$calls) {
            $calls++;

            return 'expensive-value';
        });
        self::assertSame('expensive-value', $value);

        $value2 = $store->remember('computed', 60, function () use (&$calls) {
            $calls++;

            return 'expensive-value';
        });
        self::assertSame('expensive-value', $value2);
        self::assertSame(1, $calls);
    }

    public function test_increment_decrement(): void
    {
        $store = Cache::store('lytecache');

        self::assertSame(1, $store->increment('hits'));
        self::assertSame(6, $store->increment('hits', 5));
        self::assertSame(4, $store->decrement('hits', 2));
    }

    public function test_forever(): void
    {
        $store = Cache::store('lytecache');
        $store->forever('k', 'v');
        self::assertSame('v', $store->get('k'));
    }

    public function test_forget_and_flush(): void
    {
        $store = Cache::store('lytecache');
        $store->put('a', 1, 60);
        $store->put('b', 2, 60);

        self::assertTrue($store->forget('a'));
        self::assertNull($store->get('a'));

        $store->flush();
        self::assertNull($store->get('b'));
    }

    public function test_many(): void
    {
        $store = Cache::store('lytecache');
        $store->put('a', '1', 60);
        $store->put('b', '2', 60);

        $result = $store->many(['a', 'b', 'missing']);
        self::assertSame(['a' => '1', 'b' => '2', 'missing' => null], $result);
    }

    public function test_cache_lock_mutual_exclusion(): void
    {
        $store = Cache::store('lytecache');

        $lock = $store->lock('resource', 10);
        self::assertTrue($lock->get());

        $secondLock = $store->lock('resource', 10);
        self::assertFalse($secondLock->get());

        self::assertTrue($lock->release());

        $thirdLock = $store->lock('resource', 10);
        self::assertTrue($thirdLock->get());
        $thirdLock->release();
    }

    public function test_cache_lock_block_throws_on_timeout(): void
    {
        $store = Cache::store('lytecache');
        $lock = $store->lock('resource', 10);
        self::assertTrue($lock->get());

        $this->expectException(LockTimeoutException::class);
        $store->lock('resource', 10)->block(1);
    }

    public function test_config_path_is_respected(): void
    {
        $cache = $this->app->make(LyteCache::class);
        $expected = config('cache.stores.lytecache.path');
        self::assertSame($expected, $cache->path());
    }

    public function test_artisan_maintain_command_runs(): void
    {
        Cache::store('lytecache')->put('k', 'v', 0.01);
        usleep(50_000);

        $this->artisan('lytecache:maintain')->assertSuccessful();
    }

    public function test_lyte_cache_is_resolvable_via_dependency_injection(): void
    {
        $cache = $this->app->make(LyteCache::class);
        self::assertInstanceOf(LyteCache::class, $cache);
    }
}
