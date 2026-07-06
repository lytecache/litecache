<?php

declare(strict_types=1);

// Usage: php incr-worker.php <dbPath> <key> <iterations>
// Re-exec'd as a real child process by MultiProcessTest to hammer incr()
// on one shared key from many separate OS processes.

require __DIR__.'/../../vendor/autoload.php';

use Lytecache\LyteCache;

[, $dbPath, $key, $iterations] = $argv;

$cache = new LyteCache(path: $dbPath, sweepInterval: null);

for ($i = 0; $i < (int) $iterations; $i++) {
    $cache->incr($key, 1);
}

$cache->close();
exit(0);
