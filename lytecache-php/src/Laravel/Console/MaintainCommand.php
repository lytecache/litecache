<?php

declare(strict_types=1);

namespace Lytecache\Laravel\Console;

use Illuminate\Console\Command;
use Lytecache\LyteCache;

/**
 * php artisan lytecache:maintain
 *
 * Runs LyteCache::maintain(): removes expired keys and enforces eviction
 * limits. PHP has no background threads, so this -- run on Laravel's
 * scheduler -- is what replaces the background sweeper the other
 * language implementations run automatically:
 *
 *     $schedule->command('lytecache:maintain')->everyMinute();
 */
final class MaintainCommand extends Command
{
    protected $signature = 'lytecache:maintain';

    protected $description = 'Remove expired lytecache keys and enforce eviction limits';

    public function handle(LyteCache $cache): int
    {
        $cache->maintain();

        $this->info('lytecache: maintenance complete ('.$cache->path().').');

        return self::SUCCESS;
    }
}
