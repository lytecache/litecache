<?php

declare(strict_types=1);

namespace Lytecache\Tests\Feature;

use Lytecache\LyteCache;
use Lytecache\Tests\TestCase;

/**
 * Spawns real PHP CLI child processes (proc_open) to exercise
 * cross-process atomicity -- goroutine/thread-level concurrency within
 * one process proves nothing about correctness across the PHP-FPM
 * worker processes this library actually targets.
 */
final class MultiProcessTest extends TestCase
{
    private const PROCESSES = 4;

    private const ITERATIONS_EACH = 50;

    public function test_incr_atomicity_across_processes(): void
    {
        $dbPath = $this->tempDbPath();
        $php = PHP_BINARY;
        $script = __DIR__.'/../fixtures/incr-worker.php';

        $handles = [];
        for ($i = 0; $i < self::PROCESSES; $i++) {
            $cmd = [$php, $script, $dbPath, 'counter', (string) self::ITERATIONS_EACH];
            $process = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            self::assertIsResource($process);
            $handles[] = [$process, $pipes];
        }

        foreach ($handles as [$process, $pipes]) {
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
            self::assertSame(0, $exitCode, "worker failed: {$stderr}");
        }

        $cache = new LyteCache(path: $dbPath);
        $want = self::PROCESSES * self::ITERATIONS_EACH;
        self::assertSame($want, $cache->get('counter'));
        $cache->close();
    }

    public function test_lock_mutual_exclusion_across_processes(): void
    {
        $dbPath = $this->tempDbPath();
        $logPath = sys_get_temp_dir().'/lytecache-lock-log-'.bin2hex(random_bytes(8)).'.txt';
        touch($logPath);

        $php = PHP_BINARY;
        $script = __DIR__.'/../fixtures/lock-worker.php';

        $handles = [];
        for ($i = 0; $i < self::PROCESSES; $i++) {
            $cmd = [$php, $script, $dbPath, 'resource', $logPath];
            $process = proc_open($cmd, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);
            self::assertIsResource($process);
            $handles[] = [$process, $pipes];
        }

        foreach ($handles as [$process, $pipes]) {
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            $exitCode = proc_close($process);
            self::assertSame(0, $exitCode, "worker failed: {$stderr}");
        }

        $lines = array_filter(explode("\n", (string) file_get_contents($logPath)));
        self::assertCount(self::PROCESSES * 2, $lines);

        $held = false;
        foreach ($lines as $line) {
            if ($line === 'START') {
                self::assertFalse($held, 'observed a START while the lock was already held');
                $held = true;
            } elseif ($line === 'END') {
                self::assertTrue($held, 'observed an END without a matching START');
                $held = false;
            }
        }

        @unlink($logPath);
    }
}
