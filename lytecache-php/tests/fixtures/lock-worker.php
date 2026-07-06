<?php

declare(strict_types=1);

// Usage: php lock-worker.php <dbPath> <lockName> <logPath>
// Re-exec'd as a real child process by MultiProcessTest: acquires the
// named lock, appends a START marker, holds it briefly, appends an END
// marker, then releases. A single fwrite() of a short line is atomic
// under POSIX O_APPEND, so concurrent processes' lines never interleave
// mid-line -- what the mutual-exclusion assertion relies on.

require __DIR__.'/../../vendor/autoload.php';

use Lytecache\LyteCache;

[, $dbPath, $lockName, $logPath] = $argv;

$cache = new LyteCache(path: $dbPath, sweepInterval: null);

$lock = $cache->lock($lockName, 10.0);

$fh = fopen($logPath, 'a');
fwrite($fh, "START\n");
fclose($fh);

usleep(50_000);

$fh = fopen($logPath, 'a');
fwrite($fh, "END\n");
fclose($fh);

$lock->release();
$cache->close();
exit(0);
