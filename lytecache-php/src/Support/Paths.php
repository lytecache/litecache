<?php

declare(strict_types=1);

namespace Lytecache\Support;

/**
 * Default database path resolution, shared by LyteCache and its static
 * defaultPath() accessor.
 */
final class Paths
{
    private const ENV_OVERRIDE = 'LYTECACHE_PATH';

    /**
     * Resolves the default database file location:
     * "<platform cache dir>/lytecache/<project-id>.db", or the
     * LYTECACHE_PATH environment variable if set (after "~" expansion,
     * but not otherwise forced to an absolute path -- a relative
     * LYTECACHE_PATH stays relative, matching the other implementations).
     */
    public static function defaultPath(): string
    {
        $override = getenv(self::ENV_OVERRIDE);
        if ($override !== false && $override !== '') {
            return self::expandHome($override);
        }

        $cacheDir = self::platformCacheDir();
        $projectId = self::projectId();

        return $cacheDir.DIRECTORY_SEPARATOR.'lytecache'.DIRECTORY_SEPARATOR.$projectId.'.db';
    }

    private static function platformCacheDir(): string
    {
        return match (PHP_OS_FAMILY) {
            'Darwin' => self::homeDir().'/Library/Caches',
            'Windows' => self::windowsLocalAppData(),
            default => self::xdgCacheHome(),
        };
    }

    private static function windowsLocalAppData(): string
    {
        $localAppData = getenv('LOCALAPPDATA');
        if ($localAppData !== false && $localAppData !== '') {
            return $localAppData;
        }

        return self::homeDir().'\\AppData\\Local';
    }

    private static function xdgCacheHome(): string
    {
        $xdg = getenv('XDG_CACHE_HOME');
        if ($xdg !== false && $xdg !== '') {
            return $xdg;
        }

        return self::homeDir().'/.cache';
    }

    private static function homeDir(): string
    {
        $home = getenv('HOME');
        if ($home !== false && $home !== '') {
            return $home;
        }

        // Windows fallback when HOME isn't set -- USERPROFILE is the native variable there.
        $userProfile = getenv('USERPROFILE');
        if ($userProfile !== false && $userProfile !== '') {
            return $userProfile;
        }

        throw new \RuntimeException('lytecache: could not determine the home directory to derive the default cache path');
    }

    /**
     * The first 12 hex characters of the SHA-256 hash of the resolved,
     * absolute current working directory -- the same derivation used by
     * the Python, Java, Node.js, and Go implementations of lytecache, so
     * a process in any of those languages started from the same
     * directory resolves to the same file.
     */
    public static function projectId(): string
    {
        $cwd = getcwd();
        if ($cwd === false) {
            $cwd = '.';
        }

        $resolved = realpath($cwd);
        if ($resolved === false) {
            $resolved = $cwd;
        }

        return substr(hash('sha256', $resolved), 0, 12);
    }

    /** Expands a leading "~" or "~/" to the user's home directory. */
    public static function expandHome(string $path): string
    {
        if ($path === '~') {
            return self::homeDir();
        }

        if (str_starts_with($path, '~/') || str_starts_with($path, '~'.DIRECTORY_SEPARATOR)) {
            return self::homeDir().DIRECTORY_SEPARATOR.substr($path, 2);
        }

        return $path;
    }
}
