<?php

declare(strict_types=1);

namespace Lytecache\Tests\Feature;

use Lytecache\LyteCache;
use Lytecache\Tests\TestCase;

final class PersistenceTest extends TestCase
{
    public function test_data_survives_close_and_reopen(): void
    {
        $path = $this->tempDbPath();

        $cache1 = new LyteCache(path: $path);
        $cache1->set('k', 'value');
        $cache1->incr('hits', 7);
        $cache1->close();

        $cache2 = new LyteCache(path: $path);
        self::assertSame('value', $cache2->get('k'));
        self::assertSame(7, $cache2->get('hits'));
        $cache2->close();
    }
}
