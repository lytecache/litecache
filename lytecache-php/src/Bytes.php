<?php

declare(strict_types=1);

namespace Lytecache;

/**
 * Wraps a raw binary string so it is stored as type code 0 (bytes) instead
 * of type code 1 (UTF-8 string). PHP strings are byte sequences with no
 * built-in text/binary distinction, so this wrapper is how a caller tells
 * lytecache "this is raw binary data, not text":
 *
 *     $cache->set('blob', new Bytes($rawBinaryString));
 *     $raw = $cache->get('blob'); // returns a plain string of the raw bytes
 *
 * On read, a code-0 value is returned as a plain PHP string (not
 * re-wrapped in Bytes) -- the wrapper only matters for picking the type
 * code at write time.
 */
final class Bytes
{
    public function __construct(public readonly string $value) {}
}
