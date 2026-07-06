<?php

declare(strict_types=1);

namespace Lytecache\Tests\Unit;

use Lytecache\Bytes;
use Lytecache\Exceptions\SerializationException;
use Lytecache\LyteCache;
use Lytecache\Tests\TestCase;

final class SerializationTest extends TestCase
{
    public function test_bytes_round_trip(): void
    {
        $cache = $this->newCache();
        $raw = "\x00\x01\x02\xff\x80";
        $cache->set('k', new Bytes($raw));
        self::assertSame($raw, $cache->get('k'));
    }

    public function test_string_round_trip(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 'hello, 世界');
        self::assertSame('hello, 世界', $cache->get('k'));
    }

    public function test_int_round_trip(): void
    {
        $cache = $this->newCache();
        $cache->set('k', -12345);
        self::assertSame(-12345, $cache->get('k'));
    }

    public function test_float_round_trip(): void
    {
        $cache = $this->newCache();
        $cache->set('k', 2.71828);
        self::assertSame(2.71828, $cache->get('k'));
    }

    public function test_array_round_trip(): void
    {
        $cache = $this->newCache();
        $cache->set('k', [1, 2, 3]);
        self::assertSame([1, 2, 3], $cache->get('k'));
    }

    public function test_json_serializable_round_trip(): void
    {
        $cache = $this->newCache();
        $obj = new class implements \JsonSerializable
        {
            public function jsonSerialize(): array
            {
                return ['custom' => true, 'value' => 42];
            }
        };

        $cache->set('k', $obj);
        self::assertSame(['custom' => true, 'value' => 42], $cache->get('k'));
    }

    public function test_plain_object_public_properties_round_trip(): void
    {
        $cache = $this->newCache();
        $obj = new class
        {
            public string $name = 'Ada';

            public int $age = 30;
        };

        $cache->set('k', $obj);
        self::assertSame(['name' => 'Ada', 'age' => 30], $cache->get('k'));
    }

    public function test_date_time_immutable_round_trip_as_rfc3339(): void
    {
        $cache = $this->newCache();
        $dt = new \DateTimeImmutable('2024-03-15T12:30:00+00:00');
        $cache->set('k', $dt);

        $raw = $this->rawStoredValue($cache, 'k');
        self::assertSame($dt->format(\DATE_RFC3339), trim($raw, '"'));

        $decoded = $cache->get('k', class: \DateTimeImmutable::class);
        self::assertInstanceOf(\DateTimeImmutable::class, $decoded);
        self::assertSame($dt->getTimestamp(), $decoded->getTimestamp());
    }

    public function test_backed_enum_stores_its_value(): void
    {
        $cache = $this->newCache();
        $cache->set('k', Status::Active);
        self::assertSame('active', $cache->get('k'));
    }

    public function test_backed_enum_typed_rehydration(): void
    {
        $cache = $this->newCache();
        $cache->set('k', Status::Active);
        $status = $cache->get('k', class: Status::class);
        self::assertSame(Status::Active, $status);
    }

    public function test_typed_rehydration_success(): void
    {
        $cache = $this->newCache();
        $cache->set('k', ['name' => 'Ada', 'age' => 30]);
        $person = $cache->get('k', class: Person::class);

        self::assertInstanceOf(Person::class, $person);
        self::assertSame('Ada', $person->name);
        self::assertSame(30, $person->age);
    }

    public function test_typed_rehydration_shape_mismatch_throws_in_strict_mode(): void
    {
        $cache = $this->newCache(strict: true);
        $cache->set('k', ['wrong' => 'shape']);

        $this->expectException(SerializationException::class);
        $cache->get('k', class: Person::class);
    }

    public function test_na_n_rejected_at_write(): void
    {
        $cache = $this->newCache();
        $this->expectException(SerializationException::class);
        $cache->set('k', NAN);
    }

    public function test_inf_rejected_at_write(): void
    {
        $cache = $this->newCache();
        $this->expectException(SerializationException::class);
        $cache->set('k', INF);
    }

    public function test_fake_code5_row_throws_serialization_exception(): void
    {
        $cache = $this->newCache();
        $cache->set('bootstrap', 'x'); // ensure schema/file exist
        $cache->delete('bootstrap');

        $pdo = new \PDO('sqlite:'.$cache->path());
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $now = (int) round(microtime(true) * 1000);
        $stmt = $pdo->prepare(
            "INSERT INTO cache (key, namespace, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
             VALUES ('pickle', 'default', :value, 5, :now, NULL, :now2, 0, :size)"
        );
        $stmt->bindValue(':value', 'whatever a pickle looks like', \PDO::PARAM_LOB);
        $stmt->bindValue(':now', $now);
        $stmt->bindValue(':now2', $now);
        $stmt->bindValue(':size', 28);
        $stmt->execute();

        $strictCache = new LyteCache(path: $cache->path(), strict: true);
        $this->expectException(SerializationException::class);
        $strictCache->get('pickle');
    }

    private function rawStoredValue(LyteCache $cache, string $key): string
    {
        $pdo = new \PDO('sqlite:'.$cache->path());
        $pdo->exec('PRAGMA busy_timeout = 5000');
        $stmt = $pdo->prepare("SELECT value FROM cache WHERE namespace = 'default' AND key = :key");
        $stmt->execute([':key' => $key]);

        return (string) $stmt->fetchColumn();
    }
}

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}

final class Person
{
    public function __construct(
        public readonly string $name,
        public readonly int $age,
    ) {}
}
