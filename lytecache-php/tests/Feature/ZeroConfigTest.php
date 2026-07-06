<?php

declare(strict_types=1);

namespace Lytecache\Tests\Feature;

use Lytecache\LyteCache;
use Lytecache\Tests\TestCase;

final class ZeroConfigTest extends TestCase
{
    private ?string $originalHome = null;

    private ?string $originalXdg = null;

    private ?string $originalLytecachePath = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->originalHome = getenv('HOME') ?: null;
        $this->originalXdg = getenv('XDG_CACHE_HOME') ?: null;
        $this->originalLytecachePath = getenv('LYTECACHE_PATH') ?: null;
    }

    protected function tearDown(): void
    {
        $this->restoreEnv('HOME', $this->originalHome);
        $this->restoreEnv('XDG_CACHE_HOME', $this->originalXdg);
        $this->restoreEnv('LYTECACHE_PATH', $this->originalLytecachePath);
        parent::tearDown();
    }

    private function restoreEnv(string $name, ?string $value): void
    {
        if ($value === null) {
            putenv($name);
        } else {
            putenv("{$name}={$value}");
        }
    }

    public function test_zero_config_creates_file_and_parent_dirs(): void
    {
        $home = sys_get_temp_dir().'/lytecache-home-'.bin2hex(random_bytes(8));
        mkdir($home, 0755, true);
        putenv("HOME={$home}");
        putenv('XDG_CACHE_HOME');

        $path = LyteCache::defaultPath();
        self::assertFileDoesNotExist($path);

        $cache = new LyteCache(path: $path);
        self::assertFileExists($path);
        self::assertSame($path, $cache->path());

        $cache->close();
        $this->removeDirRecursive($home);
    }

    public function test_lytecache_path_env_override(): void
    {
        $dir = sys_get_temp_dir().'/lytecache-override-'.bin2hex(random_bytes(8));
        $override = $dir.'/custom/nested/path.db';
        putenv("LYTECACHE_PATH={$override}");

        self::assertSame($override, LyteCache::defaultPath());

        $cache = new LyteCache;
        self::assertSame($override, $cache->path());
        self::assertFileExists($override);

        $cache->close();
        $this->removeDirRecursive($dir);
    }

    public function test_different_working_directories_resolve_different_files(): void
    {
        $home = sys_get_temp_dir().'/lytecache-home-'.bin2hex(random_bytes(8));
        mkdir($home, 0755, true);
        putenv("HOME={$home}");
        putenv('XDG_CACHE_HOME');
        putenv('LYTECACHE_PATH');

        $base = sys_get_temp_dir().'/lytecache-cwd-'.bin2hex(random_bytes(8));
        $dirA = $base.'/project-a';
        $dirB = $base.'/project-b';
        mkdir($dirA, 0755, true);
        mkdir($dirB, 0755, true);

        $originalCwd = getcwd();

        chdir($dirA);
        $pathA = LyteCache::defaultPath();
        $pathAAgain = LyteCache::defaultPath();
        self::assertSame($pathA, $pathAAgain);

        chdir($dirB);
        $pathB = LyteCache::defaultPath();

        chdir((string) $originalCwd);

        self::assertNotSame($pathA, $pathB);

        $this->removeDirRecursive($home);
        $this->removeDirRecursive($base);
    }

    private function removeDirRecursive(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $items = scandir($dir) ?: [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            is_dir($path) ? $this->removeDirRecursive($path) : @unlink($path);
        }

        @rmdir($dir);
    }
}
