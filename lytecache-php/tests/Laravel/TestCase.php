<?php

declare(strict_types=1);

namespace Lytecache\Tests\Laravel;

use Lytecache\Laravel\LytecacheServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** @var string[] */
    private array $tempDirs = [];

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [LytecacheServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $dir = sys_get_temp_dir().'/lytecache-laravel-test-'.bin2hex(random_bytes(8));
        mkdir($dir, 0755, true);
        $this->tempDirs[] = $dir;

        $app['config']->set('cache.default', 'lytecache');
        $app['config']->set('cache.stores.lytecache', [
            'driver' => 'lytecache',
            'path' => $dir.'/cache.db',
            'namespace' => 'default',
        ]);
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

        foreach (scandir($dir) ?: [] as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            is_dir($path) ? $this->removeDir($path) : @unlink($path);
        }

        @rmdir($dir);
    }
}
