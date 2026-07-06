<?php

declare(strict_types=1);

namespace Lytecache\Support;

use Lytecache\Bytes;
use Lytecache\Exceptions\SerializationException;

/**
 * Encodes PHP values to (bytes, type code) pairs for storage, and decodes
 * them back. See SPEC.md for the full encoding table.
 */
final class Serializer
{
    /**
     * @return array{0: string, 1: int} [encoded bytes, type code]
     */
    public static function encode(mixed $value): array
    {
        if ($value instanceof Bytes) {
            return [$value->value, Schema::TYPE_BYTES];
        }

        if (is_string($value)) {
            return [$value, Schema::TYPE_STRING];
        }

        if (is_int($value)) {
            return [(string) $value, Schema::TYPE_INT];
        }

        if (is_float($value)) {
            if (is_nan($value) || is_infinite($value)) {
                throw new SerializationException('lytecache: cannot store NAN or INF');
            }

            return [self::encodeFloat($value), Schema::TYPE_FLOAT];
        }

        // bool, array, null, and objects all become JSON (type code 4).
        $prepared = self::prepareForJson($value);

        try {
            $json = json_encode($prepared, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SerializationException('lytecache: failed to encode value as JSON: '.$e->getMessage(), previous: $e);
        }

        return [$json, Schema::TYPE_JSON];
    }

    /**
     * Stores a float as UTF-8 decimal text, not binary -- this is what
     * lets incr()/decr() be a single atomic SQL UPSERT (see
     * LyteCache::atomicIncr()). PHP's json_encode already produces the
     * shortest round-trip decimal representation of a float (with the
     * default serialize_precision=-1), so it is reused here rather than
     * reimplementing float formatting.
     */
    private static function encodeFloat(float $value): string
    {
        return json_encode($value, JSON_THROW_ON_ERROR);
    }

    /**
     * Recursively converts a value into something json_encode will turn
     * into the cross-language-conventional shape: DateTimeInterface as an
     * RFC 3339 string, a backed enum as its scalar value, and any
     * JsonSerializable left alone so json_encode calls jsonSerialize()
     * itself.
     */
    private static function prepareForJson(mixed $value): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format(DATE_RFC3339);
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof \UnitEnum) {
            // A pure (non-backed) enum has no portable scalar value; the
            // case name is the closest honest representation available.
            return $value->name;
        }

        if ($value instanceof \JsonSerializable) {
            return $value;
        }

        if (is_object($value)) {
            $result = [];
            foreach (get_object_vars($value) as $k => $v) {
                $result[$k] = self::prepareForJson($v);
            }

            return $result;
        }

        if (is_array($value)) {
            $result = [];
            foreach ($value as $k => $v) {
                $result[$k] = self::prepareForJson($v);
            }

            return $result;
        }

        return $value;
    }

    /**
     * Decodes stored bytes tagged with $typeCode. $class requests typed
     * rehydration for a JSON-coded (type 4) value; it is ignored for
     * other type codes.
     *
     * @param  class-string|null  $class
     */
    public static function decode(string $data, int $typeCode, ?string $class = null): mixed
    {
        return match ($typeCode) {
            Schema::TYPE_BYTES, Schema::TYPE_STRING => $data,
            Schema::TYPE_INT => self::decodeInt($data),
            Schema::TYPE_FLOAT => self::decodeFloat($data),
            Schema::TYPE_JSON => self::decodeJson($data, $class),
            Schema::TYPE_PYTHON_PICKLE => throw new SerializationException(
                'lytecache: value_type=5 is a Python-pickle-only format and cannot be read from PHP'
            ),
            Schema::TYPE_JAVA_SERIALIZED => throw new SerializationException(
                'lytecache: value_type=6 is a Java-serialized-only format and cannot be read from PHP'
            ),
            default => throw new SerializationException("lytecache: unknown value_type={$typeCode}"),
        };
    }

    private static function decodeInt(string $data): int
    {
        if (preg_match('/^-?\d+$/', $data) !== 1) {
            throw new SerializationException("lytecache: stored int value is not valid: {$data}");
        }

        return (int) $data;
    }

    /**
     * Accepts the NaN/Infinity spellings written by the Python (nan/inf)
     * and Java (NaN/Infinity) implementations case-insensitively -- this
     * implementation never writes those spellings itself (see
     * encodeFloat), but still needs to read a value written by one of the
     * other implementations.
     */
    private static function decodeFloat(string $data): float
    {
        $lower = strtolower($data);

        return match ($lower) {
            'nan' => NAN,
            'inf', 'infinity' => INF,
            '-inf', '-infinity' => -INF,
            default => self::parseFloatStrict($data),
        };
    }

    private static function parseFloatStrict(string $data): float
    {
        if (! is_numeric($data)) {
            throw new SerializationException("lytecache: stored float value is not valid: {$data}");
        }

        return (float) $data;
    }

    /**
     * @param  class-string|null  $class
     */
    private static function decodeJson(string $data, ?string $class): mixed
    {
        try {
            $decoded = json_decode($data, associative: true, flags: JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new SerializationException('lytecache: failed to decode stored JSON: '.$e->getMessage(), previous: $e);
        }

        if ($class === null) {
            return $decoded;
        }

        return Hydrator::hydrate($decoded, $class);
    }
}
