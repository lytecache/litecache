<?php

declare(strict_types=1);

namespace Lytecache\Laravel;

use Illuminate\Support\ServiceProvider;
use Lytecache\Eviction;
use Lytecache\Laravel\Console\MaintainCommand;
use Lytecache\LyteCache;

final class LytecacheServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Merged into config('cache.stores.lytecache') so the driver
        // works out of the box (CACHE_STORE=lytecache) with no publishing
        // step -- the same zero-configuration principle as the core
        // library itself. Publishing the config file lets a user override
        // it explicitly instead of editing config/cache.php directly.
        $this->mergeConfigFrom(__DIR__.'/../../config/lytecache.php', 'cache.stores.lytecache');

        $this->app->singleton(LyteCache::class, function ($app): LyteCache {
            /** @var array<string, mixed> $config */
            $config = $app['config']->get('cache.stores.lytecache', []);

            return new LyteCache(
                path: $config['path'] ?? null,
                namespace: $config['namespace'] ?? 'default',
                maxKeys: $config['max_keys'] ?? null,
                maxBytes: $config['max_bytes'] ?? null,
                eviction: self::evictionFromConfig($config['eviction'] ?? null),
                sweepInterval: array_key_exists('sweep_interval', $config) ? $config['sweep_interval'] : 60.0,
                strict: $config['strict'] ?? false,
            );
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../config/lytecache.php' => $this->app->configPath('lytecache.php'),
        ], 'lytecache-config');

        if ($this->app->runningInConsole()) {
            $this->commands([MaintainCommand::class]);
        }

        $this->app->make('cache')->extend('lytecache', function ($app, array $config) {
            $prefix = $config['prefix'] ?? $app['config']->get('cache.prefix', '');
            $store = new LytecacheStore($app->make(LyteCache::class), $prefix);

            return $app->make('cache')->repository($store, $config);
        });
    }

    private static function evictionFromConfig(?string $value): Eviction
    {
        return match ($value) {
            'ttl' => Eviction::TTL,
            'random' => Eviction::Random,
            'noeviction' => Eviction::NoEviction,
            default => Eviction::LRU,
        };
    }
}
