<?php

declare(strict_types=1);

namespace Lytecache\Tests\Unit;

use Lytecache\Exceptions\NotNumericException;
use Lytecache\Tests\TestCase;

final class CountersTest extends TestCase
{
    public function test_incr_starts_missing_keys_at_zero(): void
    {
        $cache = $this->newCache();
        self::assertSame(1, $cache->incr('hits'));
    }

    public function test_incr_accumulates(): void
    {
        $cache = $this->newCache();
        $cache->incr('hits', 5);
        self::assertSame(8, $cache->incr('hits', 3));
    }

    public function test_decr(): void
    {
        $cache = $this->newCache();
        $cache->incr('hits', 10);
        self::assertSame(7, $cache->decr('hits', 3));
    }

    public function test_incr_negative_amount(): void
    {
        $cache = $this->newCache();
        self::assertSame(-5, $cache->incr('hits', -5));
    }

    public function test_incr_float(): void
    {
        $cache = $this->newCache();
        self::assertSame(0.5, $cache->incrFloat('ratio', 0.5));
        self::assertSame(0.75, $cache->incrFloat('ratio', 0.25));
    }

    public function test_incr_float_on_existing_int(): void
    {
        $cache = $this->newCache();
        $cache->incr('n', 5);
        self::assertSame(5.5, $cache->incrFloat('n', 0.5));
    }

    public function test_incr_on_non_numeric_value_throws(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 'not a number');
        $this->expectException(NotNumericException::class);
        $cache->incr('k');
    }

    public function test_incr_on_existing_float_throws(): void
    {
        $cache = $this->newCache();
        $cache->incrFloat('k', 1.5);
        $this->expectException(NotNumericException::class);
        $cache->incr('k');
    }

    public function test_incr_float_on_non_numeric_value_throws(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 'nope');
        $this->expectException(NotNumericException::class);
        $cache->incrFloat('k', 1.0);
    }

    public function test_incr_on_expired_key_starts_over(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 100, ttl: 0.0);
        self::assertSame(1, $cache->incr('k'));
    }
}
