<?php

declare(strict_types=1);

namespace Lytecache\Tests\Unit;

use Lytecache\Exceptions\SerializationException;
use Lytecache\LyteCache;
use Lytecache\Tests\TestCase;

final class BasicTest extends TestCase
{
    public function test_set_get_string(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 'value');
        self::assertSame('value', $cache->get('k'));
    }

    public function test_set_get_array(): void
    {
        $cache = $this->newCache();
        $cache->set('k', ['name' => 'Ada', 'age' => 30]);
        self::assertSame(['name' => 'Ada', 'age' => 30], $cache->get('k'));
    }

    public function test_set_get_int(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 42);
        self::assertSame(42, $cache->get('k'));
    }

    public function test_set_get_float(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 3.14);
        self::assertSame(3.14, $cache->get('k'));
    }

    public function test_set_get_bool(): void
    {
        $cache = $this->newCache();
        $cache->set('k', true);
        self::assertTrue($cache->get('k'));
    }

    public function test_set_get_null(): void
    {
        $cache = $this->newCache();
        $cache->set('k', null);
        self::assertNull($cache->get('k', 'not-found-sentinel'));
    }

    public function test_get_missing_returns_default(): void
    {
        $cache = $this->newCache();
        self::assertSame('default', $cache->get('missing', 'default'));
        self::assertNull($cache->get('missing'));
    }

    public function test_delete(): void
    {
        $cache = $this->newCache();
        $cache->set('a', 1);
        $cache->set('b', 2);
        $count = $cache->delete('a', 'b', 'missing');
        self::assertSame(2, $count);
        self::assertFalse($cache->has('a'));
    }

    public function test_has(): void
    {
        $cache = $this->newCache();
        self::assertFalse($cache->has('k'));
        $cache->set('k', 'v');
        self::assertTrue($cache->has('k'));
    }

    public function test_overwrite(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 'first');
        $cache->set('k', 'second');
        self::assertSame('second', $cache->get('k'));
    }

    public function test_add_only_if_absent(): void
    {
        $cache = $this->newCache();
        self::assertTrue($cache->add('k', 'first'));
        self::assertFalse($cache->add('k', 'second'));
        self::assertSame('first', $cache->get('k'));
    }

    public function test_replace_only_if_present(): void
    {
        $cache = $this->newCache();
        self::assertFalse($cache->replace('missing', 'value'));
        $cache->set('k', 'first');
        self::assertTrue($cache->replace('k', 'second'));
        self::assertSame('second', $cache->get('k'));
    }

    public function test_get_set(): void
    {
        $cache = $this->newCache();
        self::assertNull($cache->getSet('k', 'new'));
        self::assertSame('new', $cache->getSet('k', 'newer'));
        self::assertSame('newer', $cache->get('k'));
    }

    public function test_set_many_get_many(): void
    {
        $cache = $this->newCache();
        $cache->setMany(['a' => '1', 'b' => '2']);
        $result = $cache->getMany(['a', 'b', 'missing']);
        self::assertSame(['a' => '1', 'b' => '2'], $result);
    }

    public function test_flush(): void
    {
        $cache = $this->newCache();
        $cache->setMany(['a' => 1, 'b' => 2]);
        $cache->flush();
        self::assertFalse($cache->has('a'));
        self::assertFalse($cache->has('b'));
    }

    public function test_namespace_isolation(): void
    {
        $dbPath = $this->tempDbPath();
        $ns1 = new LyteCache(path: $dbPath, namespace: 'ns1');
        $ns2 = new LyteCache(path: $dbPath, namespace: 'ns2');

        $ns1->set('k', 'ns1-value');
        self::assertFalse($ns2->has('k'));

        $ns2->set('k', 'ns2-value');
        $ns1->flush();
        self::assertSame('ns2-value', $ns2->get('k'));
    }

    public function test_close_is_idempotent(): void
    {
        $cache = $this->newCache();
        $cache->close();
        $cache->close();
        self::assertTrue(true);
    }

    public function test_path(): void
    {
        $path = $this->tempDbPath();
        $cache = new LyteCache(path: $path);
        self::assertSame($path, $cache->path());
    }

    public function test_strict_mode_throws_on_shape_mismatch(): void
    {
        $cache = $this->newCache(strict: true);
        $cache->set('k', 'a string');

        // Decoding a plain string as JSON (type mismatch scenario): force
        // via cross-compat style raw manipulation isn't needed here --
        // instead exercise the class-hydration mismatch path.
        $cache->set('shape', ['wrong' => 'field']);

        $this->expectException(SerializationException::class);
        $cache->get('shape', class: RequiresNameAndAge::class);
    }

    public function test_non_strict_mode_degrades_on_shape_mismatch(): void
    {
        $cache = $this->newCache(); // strict defaults to false
        $cache->set('shape', ['wrong' => 'field']);

        $result = $cache->get('shape', default: 'fallback', class: RequiresNameAndAge::class);
        self::assertSame('fallback', $result);
    }
}

final class RequiresNameAndAge
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {}
}
