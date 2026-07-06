<?php

declare(strict_types=1);

namespace Lytecache\Tests;

use Lytecache\LyteCache;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var string[] */
    private array $tempDirs = [];

    protected function tempDbPath(): string
    {
        $dir = sys_get_temp_dir().'/lytecache-test-'.bin2hex(random_bytes(8));
        mkdir($dir, 0755, true);
        $this->tempDirs[] = $dir;

        return $dir.'/cache.db';
    }

    protected function newCache(mixed ...$args): LyteCache
    {
        $args = ['path' => $this->tempDbPath(), ...$args];

        return new LyteCache(...$args);
    }

    protected function tearDown(): void
    {
        foreach ($this->tempDirs as $dir) {
            $this->removeDir($dir);
        }
        $this->tempDirs = [];

        parent::tearDown();
    }

    private function removeDir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir);
        if ($items === false) {
            return;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->removeDir($path);
            } else {
                @unlink($path);
            }
        }

        @rmdir($dir);
    }
}
