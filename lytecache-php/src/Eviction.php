<?php

declare(strict_types=1);

namespace Lytecache;

use Lytecache\Exceptions\CacheFullException;

/**
 * Eviction policy applied when a namespace exceeds maxKeys or maxBytes.
 */
enum Eviction
{
    /** Evict the least-recently-used key first. Default. */
    case LRU;

    /** Evict the soonest-to-expire key first; keys with no TTL are evicted last. */
    case TTL;

    /** Evict an arbitrary key. */
    case Random;

    /**
     * Reject a write that would grow the namespace past the configured
     * limit, throwing {@see CacheFullException},
     * instead of evicting. Updating an existing key is always allowed,
     * since it never grows the dataset.
     */
    case NoEviction;
}
