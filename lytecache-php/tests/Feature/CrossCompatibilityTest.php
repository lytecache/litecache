<?php

declare(strict_types=1);

namespace Lytecache\Tests\Feature;

use Lytecache\Exceptions\SerializationException;
use Lytecache\LyteCache;
use Lytecache\Tests\TestCase;

/**
 * Inserts rows via raw PDO exactly as SPEC.md describes, mirroring the
 * fixture used by the Python, Java, Node.js, and Go test suites: type
 * codes 0-4 must read back correctly, and a fake code-5 (Python-pickle
 * only) row must produce a SerializationException rather than silently
 * returning garbage.
 */
final class CrossCompatibilityTest extends TestCase
{
    public function test_raw_rows_per_spec(): void
    {
        $cache = $this->newCache();
        // Touch the cache once so the file and schema definitely exist
        // before a second, raw connection opens it.
        $cache->set('bootstrap', 'x');
        $cache->delete('bootstrap');

        $pdo = new \PDO('sqlite:'.$cache->path());
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $now = (int) round(microtime(true) * 1000);

        $insert = function (string $key, string $value, int $valueType) use ($pdo, $now): void {
            $stmt = $pdo->prepare(
                'INSERT INTO cache (key, namespace, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
                 VALUES (:key, \'default\', :value, :type, :now, NULL, :now2, 0, :size)'
            );
            $stmt->bindValue(':key', $key);
            $stmt->bindValue(':value', $value, \PDO::PARAM_LOB);
            $stmt->bindValue(':type', $valueType);
            $stmt->bindValue(':now', $now);
            $stmt->bindValue(':now2', $now);
            $stmt->bindValue(':size', strlen($value));
            $stmt->execute();
        };

        $insert('bytes', "\x01\x02\x03", 0);
        $insert('string', 'hello', 1);
        $insert('int', '42', 2);
        $insert('float', '3.14', 3);
        $insert('json', '{"a":1}', 4);
        $insert('pickle', 'whatever a Python pickle looks like', 5);

        self::assertSame("\x01\x02\x03", $cache->get('bytes'));
        self::assertSame('hello', $cache->get('string'));
        self::assertSame(42, $cache->get('int'));
        self::assertSame(3.14, $cache->get('float'));
        self::assertSame(['a' => 1], $cache->get('json'));

        $strictCache = new LyteCache(path: $cache->path(), strict: true);
        $this->expectException(SerializationException::class);
        $strictCache->get('pickle');
    }
}
