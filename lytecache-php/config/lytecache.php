<?php

declare(strict_types=1);

// This is merged into config('cache.stores.lytecache') automatically by
// LytecacheServiceProvider, so CACHE_STORE=lytecache works with no
// publishing step. Publish this file (php artisan vendor:publish --tag
// lytecache-config) if you want to edit it directly instead of editing
// config/cache.php's "stores.lytecache" entry.
return [
    'driver' => 'lytecache',

    // Where the database file lives. Laravel's storage_path() is used
    // here rather than the core library's platform-cache-dir default,
    // because that's where Laravel developers expect a cache file to
    // live: it's writable in every standard deployment, and it's
    // consistent with `php artisan cache:clear` / storage: conventions.
    'path' => storage_path('framework/cache/lytecache.db'),

    // Logical partition within the database file.
    'namespace' => 'default',

    // Evict once the namespace exceeds this many keys/bytes. Null (the
    // default) means no limit.
    'max_keys' => null,
    'max_bytes' => null,

    // "lru" (default) | "ttl" | "random" | "noeviction"
    'eviction' => 'lru',

    // Minimum seconds between opportunistic maintenance passes (see
    // LyteCache::maintain() and the lytecache:maintain artisan command).
    // Null removes that minimum.
    'sweep_interval' => 60.0,

    // false (default): a read that hits an internal error degrades to a
    // miss. true: it throws.
    'strict' => false,
];
