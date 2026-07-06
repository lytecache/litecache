<?php declare(strict_types = 1);

// odsl-/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/LyteCache.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Lytecache\LyteCache
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.5.8-cc2acd19217f7b5fec09fd8c3e7181d9cda5d6a1bbbfb6a335f074b1a2bf9509',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Lytecache\\LyteCache',
        'filename' => '/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/LyteCache.php',
      ),
    ),
    'namespace' => 'Lytecache',
    'name' => 'Lytecache\\LyteCache',
    'shortName' => 'LyteCache',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * An embedded, Redis-like cache backed by a SQLite file.
 *
 * The zero-configuration form is the flagship way to use this class:
 *
 *     $cache = new LyteCache();
 *     $cache->set(\'user:42\', [\'name\' => \'Ada\'], ttl: 300);
 *     $cache->get(\'user:42\'); // [\'name\' => \'Ada\']
 *
 * With no arguments, the database file (and any missing parent
 * directories) is created on first use, at a default, per-project
 * location -- see {@see self::defaultPath()}.
 *
 * A LyteCache instance is safe to share across many operations within one
 * PHP process/request, and safe for many PHP-FPM worker processes to use
 * concurrently against the same file: every read-modify-write operation is
 * a single SQL statement or an explicit transaction.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 34,
    'endLine' => 1334,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
      'UPSERT_SQL' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'UPSERT_SQL',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '<<<\'SQL\'
INSERT INTO cache (key, namespace, value, value_type, created_at, expires_at, last_accessed, access_count, size_bytes)
VALUES (:key, :namespace, :value, :type, :now, :expires, :now2, 0, :size)
ON CONFLICT(namespace, key) DO UPDATE SET
  value = excluded.value,
  value_type = excluded.value_type,
  created_at = excluded.created_at,
  expires_at = excluded.expires_at,
  last_accessed = excluded.last_accessed,
  access_count = 0,
  size_bytes = excluded.size_bytes
SQL',
          'attributes' => 
          array (
            'startLine' => 36,
            'endLine' => 47,
            'startTokenPos' => 73,
            'startFilePos' => 1190,
            'endTokenPos' => 75,
            'endFilePos' => 1755,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 36,
        'endLine' => 47,
        'startColumn' => 5,
        'endColumn' => 12,
      ),
      'LOCK_KEY_PREFIX' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'LOCK_KEY_PREFIX',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'__lock__:\'',
          'attributes' => 
          array (
            'startLine' => 54,
            'endLine' => 54,
            'startTokenPos' => 88,
            'startFilePos' => 2033,
            'endTokenPos' => 88,
            'endFilePos' => 2043,
          ),
        ),
        'docComment' => '/**
 * Namespaces distributed-lock keys away from ordinary user keys.
 * Public so the Laravel Lock adapter (see src/Laravel/LytecacheLock.php)
 * can build the same underlying key without duplicating this literal.
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 54,
        'endLine' => 54,
        'startColumn' => 5,
        'endColumn' => 47,
      ),
      'LOCK_POLL_SECONDS' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'LOCK_POLL_SECONDS',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '0.05',
          'attributes' => 
          array (
            'startLine' => 56,
            'endLine' => 56,
            'startTokenPos' => 99,
            'startFilePos' => 2085,
            'endTokenPos' => 99,
            'endFilePos' => 2088,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 56,
        'endLine' => 56,
        'startColumn' => 5,
        'endColumn' => 43,
      ),
      'MAINTENANCE_EVERY_OPS' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'MAINTENANCE_EVERY_OPS',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '100',
          'attributes' => 
          array (
            'startLine' => 59,
            'endLine' => 59,
            'startTokenPos' => 112,
            'startFilePos' => 2214,
            'endTokenPos' => 112,
            'endFilePos' => 2216,
          ),
        ),
        'docComment' => '/** How many operations pass between opportunistic maintenance attempts. */',
        'attributes' => 
        array (
        ),
        'startLine' => 59,
        'endLine' => 59,
        'startColumn' => 5,
        'endColumn' => 46,
      ),
      'MAINTENANCE_BATCH' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'MAINTENANCE_BATCH',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '500',
          'attributes' => 
          array (
            'startLine' => 62,
            'endLine' => 62,
            'startTokenPos' => 125,
            'startFilePos' => 2337,
            'endTokenPos' => 125,
            'endFilePos' => 2339,
          ),
        ),
        'docComment' => '/** Bounds each expired-row delete pass and each LRU-flush transaction. */',
        'attributes' => 
        array (
        ),
        'startLine' => 62,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 42,
      ),
      'MAX_EVICTION_PASSES' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'MAX_EVICTION_PASSES',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '100000',
          'attributes' => 
          array (
            'startLine' => 65,
            'endLine' => 65,
            'startTokenPos' => 138,
            'startFilePos' => 2448,
            'endTokenPos' => 138,
            'endFilePos' => 2454,
          ),
        ),
        'docComment' => '/** Defensive cap on the one-row-at-a-time eviction loop. */',
        'attributes' => 
        array (
        ),
        'startLine' => 65,
        'endLine' => 65,
        'startColumn' => 5,
        'endColumn' => 48,
      ),
    ),
    'immediateProperties' => 
    array (
      'path' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'path',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 67,
        'endLine' => 67,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'pdo' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'pdo',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'PDO',
            'isIdentifier' => false,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 69,
        'endLine' => 69,
        'startColumn' => 5,
        'endColumn' => 22,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'stmtCache' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'stmtCache',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 72,
            'endLine' => 72,
            'startTokenPos' => 167,
            'startFilePos' => 2594,
            'endTokenPos' => 168,
            'endFilePos' => 2595,
          ),
        ),
        'docComment' => '/** @var array<string, \\PDOStatement> */',
        'attributes' => 
        array (
        ),
        'startLine' => 72,
        'endLine' => 72,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'lruBuffer' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'lruBuffer',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '[]',
          'attributes' => 
          array (
            'startLine' => 75,
            'endLine' => 75,
            'startTokenPos' => 181,
            'startFilePos' => 2704,
            'endTokenPos' => 182,
            'endFilePos' => 2705,
          ),
        ),
        'docComment' => '/** @var array<string, array{lastAccessed: int, accessCount: int}> */',
        'attributes' => 
        array (
        ),
        'startLine' => 75,
        'endLine' => 75,
        'startColumn' => 5,
        'endColumn' => 34,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'hits' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'hits',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 77,
            'endLine' => 77,
            'startTokenPos' => 193,
            'startFilePos' => 2733,
            'endTokenPos' => 193,
            'endFilePos' => 2733,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 77,
        'endLine' => 77,
        'startColumn' => 5,
        'endColumn' => 26,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'misses' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'misses',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 79,
            'endLine' => 79,
            'startTokenPos' => 204,
            'startFilePos' => 2763,
            'endTokenPos' => 204,
            'endFilePos' => 2763,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 79,
        'endLine' => 79,
        'startColumn' => 5,
        'endColumn' => 28,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'evictions' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'evictions',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 81,
            'endLine' => 81,
            'startTokenPos' => 215,
            'startFilePos' => 2796,
            'endTokenPos' => 215,
            'endFilePos' => 2796,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 81,
        'endLine' => 81,
        'startColumn' => 5,
        'endColumn' => 31,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'expiredRemoved' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'expiredRemoved',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 83,
            'endLine' => 83,
            'startTokenPos' => 226,
            'startFilePos' => 2834,
            'endTokenPos' => 226,
            'endFilePos' => 2834,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 83,
        'endLine' => 83,
        'startColumn' => 5,
        'endColumn' => 36,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'opsSinceMaintenance' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'opsSinceMaintenance',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 85,
            'endLine' => 85,
            'startTokenPos' => 237,
            'startFilePos' => 2877,
            'endTokenPos' => 237,
            'endFilePos' => 2877,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 85,
        'endLine' => 85,
        'startColumn' => 5,
        'endColumn' => 41,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'lastMaintenanceAt' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'lastMaintenanceAt',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'float',
            'isIdentifier' => true,
          ),
        ),
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 87,
        'endLine' => 87,
        'startColumn' => 5,
        'endColumn' => 37,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'closed' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'closed',
        'modifiers' => 4,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => 'false',
          'attributes' => 
          array (
            'startLine' => 89,
            'endLine' => 89,
            'startTokenPos' => 255,
            'startFilePos' => 2947,
            'endTokenPos' => 255,
            'endFilePos' => 2951,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 89,
        'endLine' => 89,
        'startColumn' => 5,
        'endColumn' => 33,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'namespace' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'namespace',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\'default\'',
          'attributes' => 
          array (
            'startLine' => 107,
            'endLine' => 107,
            'startTokenPos' => 287,
            'startFilePos' => 4285,
            'endTokenPos' => 287,
            'endFilePos' => 4293,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 107,
        'endLine' => 107,
        'startColumn' => 9,
        'endColumn' => 54,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'maxKeys' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'maxKeys',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'int',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => 
        array (
          'code' => 'null',
          'attributes' => 
          array (
            'startLine' => 108,
            'endLine' => 108,
            'startTokenPos' => 301,
            'startFilePos' => 4337,
            'endTokenPos' => 301,
            'endFilePos' => 4340,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 108,
        'endLine' => 108,
        'startColumn' => 9,
        'endColumn' => 45,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'maxBytes' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'maxBytes',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'int',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => 
        array (
          'code' => 'null',
          'attributes' => 
          array (
            'startLine' => 109,
            'endLine' => 109,
            'startTokenPos' => 315,
            'startFilePos' => 4385,
            'endTokenPos' => 315,
            'endFilePos' => 4388,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 109,
        'endLine' => 109,
        'startColumn' => 9,
        'endColumn' => 46,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'eviction' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'eviction',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Lytecache\\Eviction',
            'isIdentifier' => false,
          ),
        ),
        'default' => 
        array (
          'code' => '\\Lytecache\\Eviction::LRU',
          'attributes' => 
          array (
            'startLine' => 110,
            'endLine' => 110,
            'startTokenPos' => 328,
            'startFilePos' => 4437,
            'endTokenPos' => 330,
            'endFilePos' => 4449,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 110,
        'endLine' => 110,
        'startColumn' => 9,
        'endColumn' => 59,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'sweepInterval' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'sweepInterval',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'float',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'default' => 
        array (
          'code' => '60.0',
          'attributes' => 
          array (
            'startLine' => 111,
            'endLine' => 111,
            'startTokenPos' => 344,
            'startFilePos' => 4501,
            'endTokenPos' => 344,
            'endFilePos' => 4504,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 111,
        'endLine' => 111,
        'startColumn' => 9,
        'endColumn' => 53,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'strict' => 
      array (
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'name' => 'strict',
        'modifiers' => 132,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => 'false',
          'attributes' => 
          array (
            'startLine' => 112,
            'endLine' => 112,
            'startTokenPos' => 357,
            'startFilePos' => 4547,
            'endTokenPos' => 357,
            'endFilePos' => 4551,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 112,
        'endLine' => 112,
        'startColumn' => 9,
        'endColumn' => 45,
        'isPromoted' => true,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 106,
                'endLine' => 106,
                'startTokenPos' => 274,
                'startFilePos' => 4234,
                'endTokenPos' => 274,
                'endFilePos' => 4237,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 106,
            'endLine' => 106,
            'startColumn' => 9,
            'endColumn' => 28,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
          'namespace' => 
          array (
            'name' => 'namespace',
            'default' => 
            array (
              'code' => '\'default\'',
              'attributes' => 
              array (
                'startLine' => 107,
                'endLine' => 107,
                'startTokenPos' => 287,
                'startFilePos' => 4285,
                'endTokenPos' => 287,
                'endFilePos' => 4293,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 107,
            'endLine' => 107,
            'startColumn' => 9,
            'endColumn' => 54,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'maxKeys' => 
          array (
            'name' => 'maxKeys',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 108,
                'endLine' => 108,
                'startTokenPos' => 301,
                'startFilePos' => 4337,
                'endTokenPos' => 301,
                'endFilePos' => 4340,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 108,
            'endLine' => 108,
            'startColumn' => 9,
            'endColumn' => 45,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
          'maxBytes' => 
          array (
            'name' => 'maxBytes',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 109,
                'endLine' => 109,
                'startTokenPos' => 315,
                'startFilePos' => 4385,
                'endTokenPos' => 315,
                'endFilePos' => 4388,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'int',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 109,
            'endLine' => 109,
            'startColumn' => 9,
            'endColumn' => 46,
            'parameterIndex' => 3,
            'isOptional' => true,
          ),
          'eviction' => 
          array (
            'name' => 'eviction',
            'default' => 
            array (
              'code' => '\\Lytecache\\Eviction::LRU',
              'attributes' => 
              array (
                'startLine' => 110,
                'endLine' => 110,
                'startTokenPos' => 328,
                'startFilePos' => 4437,
                'endTokenPos' => 330,
                'endFilePos' => 4449,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Lytecache\\Eviction',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 110,
            'endLine' => 110,
            'startColumn' => 9,
            'endColumn' => 59,
            'parameterIndex' => 4,
            'isOptional' => true,
          ),
          'sweepInterval' => 
          array (
            'name' => 'sweepInterval',
            'default' => 
            array (
              'code' => '60.0',
              'attributes' => 
              array (
                'startLine' => 111,
                'endLine' => 111,
                'startTokenPos' => 344,
                'startFilePos' => 4501,
                'endTokenPos' => 344,
                'endFilePos' => 4504,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 111,
            'endLine' => 111,
            'startColumn' => 9,
            'endColumn' => 53,
            'parameterIndex' => 5,
            'isOptional' => true,
          ),
          'strict' => 
          array (
            'name' => 'strict',
            'default' => 
            array (
              'code' => 'false',
              'attributes' => 
              array (
                'startLine' => 112,
                'endLine' => 112,
                'startTokenPos' => 357,
                'startFilePos' => 4547,
                'endTokenPos' => 357,
                'endFilePos' => 4551,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'bool',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 112,
            'endLine' => 112,
            'startColumn' => 9,
            'endColumn' => 45,
            'parameterIndex' => 6,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @param  string|null  $path  Explicit database file path; default: {@see self::defaultPath()}.
 * @param  string  $namespace  Logical partition within the database file.
 * @param  int|null  $maxKeys  Evict (per $eviction) once the namespace exceeds this many keys.
 * @param  int|null  $maxBytes  Evict (per $eviction) once the namespace exceeds this many bytes.
 * @param  Eviction  $eviction  Eviction policy.
 * @param  float|null  $sweepInterval  Minimum seconds between opportunistic maintenance passes;
 *                                     null removes that minimum, so maintenance runs as often as the internal operation
 *                                     counter allows. PHP has no background threads, so there is no sweeper to disable outright
 *                                     -- see maintain() and the README\'s "Why opportunistic maintenance?" section.
 * @param  bool  $strict  When false (default), a read that hits an internal deserialization
 *                        error degrades to a miss rather than throwing. When true, it throws
 *                        {@see SerializationException}. Writes always throw, in both modes.
 */',
        'startLine' => 105,
        'endLine' => 123,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'defaultPath' => 
      array (
        'name' => 'defaultPath',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Returns the default database file location for the current working
 * directory: "<platform cache dir>/lytecache/<project-id>.db".
 *
 * <project-id> is the first 12 hex characters of the SHA-256 hash of
 * the resolved, absolute current working directory -- the same
 * derivation used by the Python, Java, Node.js, and Go
 * implementations of lytecache, so a process in any of those
 * languages started from the same directory resolves to the same
 * file.
 *
 * If the LYTECACHE_PATH environment variable is set, it is returned
 * instead (after "~" expansion), taking priority over the derived
 * default.
 */',
        'startLine' => 140,
        'endLine' => 143,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'path' => 
      array (
        'name' => 'path',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** This instance\'s actual database file path. */',
        'startLine' => 146,
        'endLine' => 149,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'openWithRetry' => 
      array (
        'name' => 'openWithRetry',
        'parameters' => 
        array (
          'path' => 
          array (
            'name' => 'path',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 155,
            'endLine' => 155,
            'startColumn' => 36,
            'endColumn' => 47,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'PDO',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 155,
        'endLine' => 189,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'isSqliteBusy' => 
      array (
        'name' => 'isSqliteBusy',
        'parameters' => 
        array (
          'e' => 
          array (
            'name' => 'e',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'PDOException',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 198,
            'endLine' => 198,
            'startColumn' => 42,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Multiple processes creating the same brand-new WAL-mode file at the
 * exact same moment can hit SQLITE_BUSY on the initial journal_mode
 * switch, before busy_timeout has had a chance to matter (a
 * well-known SQLite cold-start race, not specific to this driver) --
 * openWithRetry() retries specifically on this error before giving up.
 */',
        'startLine' => 198,
        'endLine' => 206,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'checkSchemaVersion' => 
      array (
        'name' => 'checkSchemaVersion',
        'parameters' => 
        array (
          'pdo' => 
          array (
            'name' => 'pdo',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'PDO',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 208,
            'endLine' => 208,
            'startColumn' => 41,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 208,
        'endLine' => 229,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'prepare' => 
      array (
        'name' => 'prepare',
        'parameters' => 
        array (
          'sql' => 
          array (
            'name' => 'sql',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 231,
            'endLine' => 231,
            'startColumn' => 30,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'PDOStatement',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 231,
        'endLine' => 234,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'executeWithBlob' => 
      array (
        'name' => 'executeWithBlob',
        'parameters' => 
        array (
          'sql' => 
          array (
            'name' => 'sql',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 252,
            'endLine' => 252,
            'startColumn' => 38,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'params' => 
          array (
            'name' => 'params',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 252,
            'endLine' => 252,
            'startColumn' => 51,
            'endColumn' => 63,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'blobParam' => 
          array (
            'name' => 'blobParam',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 252,
            'endLine' => 252,
            'startColumn' => 66,
            'endColumn' => 82,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'blobValue' => 
          array (
            'name' => 'blobValue',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 252,
            'endLine' => 252,
            'startColumn' => 85,
            'endColumn' => 101,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'PDOStatement',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Executes $sql with $params, binding $blobValue onto $blobParam as
 * PDO::PARAM_LOB so it is always stored/read back as raw, binary-safe
 * bytes -- never re-interpreted or coerced by SQLite\'s dynamic typing.
 *
 * Wrapped in an explicit BEGIN IMMEDIATE/COMMIT rather than left as a
 * bare autocommit statement: an UPSERT is, under the hood, a
 * conflict-check *read* that may need to upgrade to a *write*
 * mid-statement, and that upgrade is exactly the SQLite scenario
 * where busy_timeout-based retries alone can spin far longer than
 * expected under real concurrent-writer load (observed empirically
 * against the multi-process test). BEGIN IMMEDIATE acquires the write
 * lock upfront instead, which avoids the upgrade entirely.
 *
 * @param  array<string, int|string|null>  $params
 */',
        'startLine' => 252,
        'endLine' => 266,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'inWriteTransaction' => 
      array (
        'name' => 'inWriteTransaction',
        'parameters' => 
        array (
          'body' => 
          array (
            'name' => 'body',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 276,
            'endLine' => 276,
            'startColumn' => 41,
            'endColumn' => 54,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Runs $body inside a single BEGIN IMMEDIATE/COMMIT transaction, with
 * jittered retry on SQLITE_BUSY around the whole attempt. $body may
 * issue several statements (e.g. a read followed by a write, as in
 * {@see getSet()}) -- callers must never nest a second call to this
 * method (or another BEGIN) inside $body, since SQLite does not support
 * nested transactions.
 */',
        'startLine' => 276,
        'endLine' => 296,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'executeWithRetry' => 
      array (
        'name' => 'executeWithRetry',
        'parameters' => 
        array (
          'fn' => 
          array (
            'name' => 'fn',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 310,
            'endLine' => 310,
            'startColumn' => 39,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Retries $fn on SQLITE_BUSY. PRAGMA busy_timeout (5000ms) already does
 * the bulk of the waiting for us at the C level, blocking inside each
 * BEGIN IMMEDIATE/COMMIT until the competing writer releases the lock
 * or the timeout elapses -- so a busy error surfacing here means a
 * writer held the lock for the *entire* 5s window, which only happens
 * under truly pathological contention. This loop is a thin safety net
 * on top of that: a handful of extra attempts with a short jittered
 * sleep, mainly to desynchronize many PHP-FPM workers that all got
 * unblocked at the same instant and would otherwise immediately
 * collide again on the next BEGIN IMMEDIATE.
 */',
        'startLine' => 310,
        'endLine' => 328,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'nowMillis' => 
      array (
        'name' => 'nowMillis',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 330,
        'endLine' => 333,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'assertOpen' => 
      array (
        'name' => 'assertOpen',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 335,
        'endLine' => 340,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'set' => 
      array (
        'name' => 'set',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 347,
            'endLine' => 347,
            'startColumn' => 25,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 347,
            'endLine' => 347,
            'startColumn' => 38,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 347,
                'endLine' => 347,
                'startTokenPos' => 1537,
                'startFilePos' => 13213,
                'endTokenPos' => 1537,
                'endFilePos' => 13216,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 347,
            'endLine' => 347,
            'startColumn' => 52,
            'endColumn' => 69,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Stores $value under $key, replacing any existing value. $ttl is in seconds. */',
        'startLine' => 347,
        'endLine' => 371,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'get' => 
      array (
        'name' => 'get',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 383,
            'endLine' => 383,
            'startColumn' => 25,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'default' => 
          array (
            'name' => 'default',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 383,
                'endLine' => 383,
                'startTokenPos' => 1770,
                'startFilePos' => 14446,
                'endTokenPos' => 1770,
                'endFilePos' => 14449,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 383,
            'endLine' => 383,
            'startColumn' => 38,
            'endColumn' => 58,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
          'class' => 
          array (
            'name' => 'class',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 383,
                'endLine' => 383,
                'startTokenPos' => 1780,
                'startFilePos' => 14469,
                'endTokenPos' => 1780,
                'endFilePos' => 14472,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'string',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 383,
            'endLine' => 383,
            'startColumn' => 61,
            'endColumn' => 81,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Reads $key. Returns $default (never throws) on a miss or expiry.
 *
 * $class requests typed rehydration of a JSON-coded value: the
 * decoded array is mapped onto $class via its constructor (matching
 * parameter names to array keys) or, failing that, by assigning
 * public properties directly. See SPEC.md for the full rules.
 *
 * @param  class-string|null  $class
 */',
        'startLine' => 383,
        'endLine' => 415,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'selectRaw' => 
      array (
        'name' => 'selectRaw',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 420,
            'endLine' => 420,
            'startColumn' => 32,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * @return array{value: string, value_type: int, expires_at: int|null}|null
 */',
        'startLine' => 420,
        'endLine' => 429,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'deleteRaw' => 
      array (
        'name' => 'deleteRaw',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 431,
            'endLine' => 431,
            'startColumn' => 32,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 431,
        'endLine' => 435,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'delete' => 
      array (
        'name' => 'delete',
        'parameters' => 
        array (
          'keys' => 
          array (
            'name' => 'keys',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => true,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 438,
            'endLine' => 438,
            'startColumn' => 28,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Deletes the given keys, returning how many actually existed. */',
        'startLine' => 438,
        'endLine' => 458,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => true,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'has' => 
      array (
        'name' => 'has',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 461,
            'endLine' => 461,
            'startColumn' => 25,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Whether $key is present and not expired. */',
        'startLine' => 461,
        'endLine' => 481,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'add' => 
      array (
        'name' => 'add',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 484,
            'endLine' => 484,
            'startColumn' => 25,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 484,
            'endLine' => 484,
            'startColumn' => 38,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 484,
                'endLine' => 484,
                'startTokenPos' => 2505,
                'startFilePos' => 17645,
                'endTokenPos' => 2505,
                'endFilePos' => 17648,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 484,
            'endLine' => 484,
            'startColumn' => 52,
            'endColumn' => 69,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Sets $key only if it is currently absent or expired (Redis "SET NX"), atomically. */',
        'startLine' => 484,
        'endLine' => 516,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'replace' => 
      array (
        'name' => 'replace',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 519,
            'endLine' => 519,
            'startColumn' => 29,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 519,
            'endLine' => 519,
            'startColumn' => 42,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 519,
                'endLine' => 519,
                'startTokenPos' => 2794,
                'startFilePos' => 18800,
                'endTokenPos' => 2794,
                'endFilePos' => 18803,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 519,
            'endLine' => 519,
            'startColumn' => 56,
            'endColumn' => 73,
            'parameterIndex' => 2,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Sets $key only if it is currently present and not expired (Redis "SET XX"), atomically. */',
        'startLine' => 519,
        'endLine' => 545,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'getSet' => 
      array (
        'name' => 'getSet',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 552,
            'endLine' => 552,
            'startColumn' => 28,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'value' => 
          array (
            'name' => 'value',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'mixed',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 552,
            'endLine' => 552,
            'startColumn' => 41,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Atomically swaps in $value and returns the previous value (or null
 * if there was none). Like Redis\'s GETSET, it clears any TTL that was
 * set on the previous value.
 */',
        'startLine' => 552,
        'endLine' => 604,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'setMany' => 
      array (
        'name' => 'setMany',
        'parameters' => 
        array (
          'entries' => 
          array (
            'name' => 'entries',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 611,
            'endLine' => 611,
            'startColumn' => 29,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => 
            array (
              'code' => 'null',
              'attributes' => 
              array (
                'startLine' => 611,
                'endLine' => 611,
                'startTokenPos' => 3374,
                'startFilePos' => 22208,
                'endTokenPos' => 3374,
                'endFilePos' => 22211,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 611,
            'endLine' => 611,
            'startColumn' => 45,
            'endColumn' => 62,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Writes every entry in a single transaction, with a shared $ttl.
 *
 * @param  array<string, mixed>  $entries
 */',
        'startLine' => 611,
        'endLine' => 640,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'getMany' => 
      array (
        'name' => 'getMany',
        'parameters' => 
        array (
          'keys' => 
          array (
            'name' => 'keys',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 649,
            'endLine' => 649,
            'startColumn' => 29,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'array',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Reads all of the given keys in a single query, skipping missing or
 * expired keys rather than erroring.
 *
 * @param  string[]  $keys
 * @return array<string, mixed>
 */',
        'startLine' => 649,
        'endLine' => 705,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'checkCapacity' => 
      array (
        'name' => 'checkCapacity',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 713,
            'endLine' => 713,
            'startColumn' => 36,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enforces maxKeys/maxBytes for the NoEviction policy, ahead of a
 * single-key write, so a rejected write for a *new* key never has a
 * side effect. Updating an existing key is always allowed, since it
 * never grows the dataset.
 */',
        'startLine' => 713,
        'endLine' => 748,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'expire' => 
      array (
        'name' => 'expire',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 755,
            'endLine' => 755,
            'startColumn' => 28,
            'endColumn' => 38,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'float',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 755,
            'endLine' => 755,
            'startColumn' => 41,
            'endColumn' => 50,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Sets or overwrites the TTL (in seconds) on an existing key. Returns whether the key existed. */',
        'startLine' => 755,
        'endLine' => 768,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'persist' => 
      array (
        'name' => 'persist',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 771,
            'endLine' => 771,
            'startColumn' => 29,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Removes any TTL from an existing key. Returns whether the key existed. */',
        'startLine' => 771,
        'endLine' => 783,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'touch' => 
      array (
        'name' => 'touch',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 786,
            'endLine' => 786,
            'startColumn' => 27,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'float',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 786,
            'endLine' => 786,
            'startColumn' => 40,
            'endColumn' => 49,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Refreshes an existing key\'s TTL (sliding expiration). Equivalent to expire(). */',
        'startLine' => 786,
        'endLine' => 789,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'ttl' => 
      array (
        'name' => 'ttl',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 796,
            'endLine' => 796,
            'startColumn' => 25,
            'endColumn' => 35,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'float',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'int',
                  'isIdentifier' => true,
                ),
              ),
              2 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Remaining time-to-live for $key, in seconds: -1 if the key exists
 * with no TTL, null if the key does not exist (or is already
 * expired).
 */',
        'startLine' => 796,
        'endLine' => 823,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'atomicIncr' => 
      array (
        'name' => 'atomicIncr',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 33,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'amountText' => 
          array (
            'name' => 'amountText',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 46,
            'endColumn' => 63,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'resultType' => 
          array (
            'name' => 'resultType',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 66,
            'endColumn' => 80,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
          'allowedTypesSql' => 
          array (
            'name' => 'allowedTypesSql',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 839,
            'endLine' => 839,
            'startColumn' => 83,
            'endColumn' => 105,
            'parameterIndex' => 3,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * A single UPSERT: correct under concurrent access from many PHP-FPM
 * worker processes sharing the file, never a read-modify-write race.
 * It relies on the value being stored as UTF-8 decimal text (see
 * Serializer::encode): CAST(value AS TEXT) reads the digits, SQLite
 * coerces them to a number for the addition, and the outer
 * CAST(... AS TEXT) converts the result back to decimal digits before
 * storing it as a BLOB again. An expired existing row is treated as
 * absent (starts from zero) rather than as an error.
 */',
        'startLine' => 839,
        'endLine' => 890,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'incr' => 
      array (
        'name' => 'incr',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 898,
            'endLine' => 898,
            'startColumn' => 26,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'amount' => 
          array (
            'name' => 'amount',
            'default' => 
            array (
              'code' => '1',
              'attributes' => 
              array (
                'startLine' => 898,
                'endLine' => 898,
                'startTokenPos' => 5181,
                'startFilePos' => 32882,
                'endTokenPos' => 5181,
                'endFilePos' => 32882,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 898,
            'endLine' => 898,
            'startColumn' => 39,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Atomically adds $amount (may be negative) to the integer stored at
 * $key, creating it (starting from 0) if absent, and returns the new
 * value. Throws {@see NotNumericException} if the existing value is
 * not an integer -- incr() never silently reinterprets a float.
 */',
        'startLine' => 898,
        'endLine' => 908,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'decr' => 
      array (
        'name' => 'decr',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 911,
            'endLine' => 911,
            'startColumn' => 26,
            'endColumn' => 36,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'amount' => 
          array (
            'name' => 'amount',
            'default' => 
            array (
              'code' => '1',
              'attributes' => 
              array (
                'startLine' => 911,
                'endLine' => 911,
                'startTokenPos' => 5292,
                'startFilePos' => 33348,
                'endTokenPos' => 5292,
                'endFilePos' => 33348,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 911,
            'endLine' => 911,
            'startColumn' => 39,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Equivalent to incr($key, -$amount). */',
        'startLine' => 911,
        'endLine' => 914,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'incrFloat' => 
      array (
        'name' => 'incrFloat',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 922,
            'endLine' => 922,
            'startColumn' => 31,
            'endColumn' => 41,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'amount' => 
          array (
            'name' => 'amount',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'float',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 922,
            'endLine' => 922,
            'startColumn' => 44,
            'endColumn' => 56,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'float',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Atomically adds $amount to the numeric value stored at $key (which
 * may be an integer or a float), creating it (starting from 0) if
 * absent, and returns the new value as a float. Throws
 * {@see NotNumericException} if the existing value is not numeric.
 */',
        'startLine' => 922,
        'endLine' => 933,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'keys' => 
      array (
        'name' => 'keys',
        'parameters' => 
        array (
          'pattern' => 
          array (
            'name' => 'pattern',
            'default' => 
            array (
              'code' => '\'*\'',
              'attributes' => 
              array (
                'startLine' => 948,
                'endLine' => 948,
                'startTokenPos' => 5453,
                'startFilePos' => 34822,
                'endTokenPos' => 5453,
                'endFilePos' => 34824,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 948,
            'endLine' => 948,
            'startColumn' => 26,
            'endColumn' => 46,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Generator',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Lazily iterates keys in the current namespace matching $pattern,
 * using SQLite\'s native GLOB syntax (*, ?, [...]) -- not SQL LIKE\'s
 * %/_ wildcards -- for consistency with the Python, Java, Node.js,
 * and Go implementations. Cursor-based (keyset pagination in batches
 * of 500), so it never loads every key into memory at once.
 *
 * @return \\Generator<int, string>
 */',
        'startLine' => 948,
        'endLine' => 981,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => true,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'flush' => 
      array (
        'name' => 'flush',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Deletes every key in the current namespace. Takes no key or pattern
 * argument by design -- to clear a subset, delete by key or pattern
 * instead (iterate keys() and call delete()).
 */',
        'startLine' => 988,
        'endLine' => 994,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'stats' => 
      array (
        'name' => 'stats',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Lytecache\\CacheStats',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 996,
        'endLine' => 1020,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'vacuum' => 
      array (
        'name' => 'vacuum',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Reclaims disk space left behind by deleted rows. */',
        'startLine' => 1023,
        'endLine' => 1027,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'close' => 
      array (
        'name' => 'close',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flushes any buffered state and closes the underlying database
 * connection. Safe to call more than once. Also called automatically
 * by __destruct().
 */',
        'startLine' => 1034,
        'endLine' => 1046,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      '__destruct' => 
      array (
        'name' => '__destruct',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1048,
        'endLine' => 1053,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'maintain' => 
      array (
        'name' => 'maintain',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Runs one maintenance pass: flush buffered LRU bookkeeping, remove
 * expired rows in bounded batches, then enforce capacity limits. PHP
 * has no background threads, so this -- rather than a sweeper -- is
 * how expired rows actually get removed from disk over time. Called
 * automatically on roughly every 100th operation (see
 * maybeMaintain()); expose it publicly too so a scheduler (e.g.
 * Laravel\'s, via the lytecache:maintain artisan command) can call it
 * on a fixed cadence regardless of traffic.
 */',
        'startLine' => 1065,
        'endLine' => 1072,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'maybeMaintain' => 
      array (
        'name' => 'maybeMaintain',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1074,
        'endLine' => 1091,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'removeExpiredBatch' => 
      array (
        'name' => 'removeExpiredBatch',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1093,
        'endLine' => 1116,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'bufferLru' => 
      array (
        'name' => 'bufferLru',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1124,
            'endLine' => 1124,
            'startColumn' => 32,
            'endColumn' => 42,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'now' => 
          array (
            'name' => 'now',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'int',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1124,
            'endLine' => 1124,
            'startColumn' => 45,
            'endColumn' => 52,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Buffers a read\'s last_accessed/access_count update in memory. It is
 * flushed in batches (every 200 buffered keys, on maintain(), on
 * maybeEvict() for the LRU policy, or on close()) rather than written
 * synchronously on every read.
 */',
        'startLine' => 1124,
        'endLine' => 1134,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'flushLruBuffer' => 
      array (
        'name' => 'flushLruBuffer',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Flushes buffered LRU bookkeeping in one transaction. LRU bookkeeping
 * is inherently best-effort -- losing an update never corrupts data,
 * it only slightly stales eviction order -- so a transient SQLITE_BUSY
 * collision (e.g. another LyteCache instance on the same file
 * flushing or closing at the same moment, most visible when several
 * instances are destructed at process/script shutdown in quick
 * succession) is swallowed rather than thrown. Any other error is
 * still rethrown, since that could indicate a real problem (a full
 * disk, a corrupt database, etc.).
 */',
        'startLine' => 1147,
        'endLine' => 1182,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'maybeEvict' => 
      array (
        'name' => 'maybeEvict',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1184,
        'endLine' => 1202,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'evictionOrderBy' => 
      array (
        'name' => 'evictionOrderBy',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1204,
        'endLine' => 1213,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'enforceCapacity' => 
      array (
        'name' => 'enforceCapacity',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'void',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 1215,
        'endLine' => 1256,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'remember' => 
      array (
        'name' => 'remember',
        'parameters' => 
        array (
          'key' => 
          array (
            'name' => 'key',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1269,
            'endLine' => 1269,
            'startColumn' => 30,
            'endColumn' => 40,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'ttl' => 
          array (
            'name' => 'ttl',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
              'data' => 
              array (
                'types' => 
                array (
                  0 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'float',
                      'isIdentifier' => true,
                    ),
                  ),
                  1 => 
                  array (
                    'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                    'data' => 
                    array (
                      'name' => 'null',
                      'isIdentifier' => true,
                    ),
                  ),
                ),
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1269,
            'endLine' => 1269,
            'startColumn' => 43,
            'endColumn' => 53,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
          'loader' => 
          array (
            'name' => 'loader',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'callable',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1269,
            'endLine' => 1269,
            'startColumn' => 56,
            'endColumn' => 71,
            'parameterIndex' => 2,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'mixed',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Reads $key; on a miss, calls $loader, stores the result with $ttl
 * (seconds), and returns it. On a hit, returns the cached value
 * without calling $loader.
 *
 * @param  callable(): mixed  $loader
 */',
        'startLine' => 1269,
        'endLine' => 1283,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'lock' => 
      array (
        'name' => 'lock',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1296,
            'endLine' => 1296,
            'startColumn' => 26,
            'endColumn' => 37,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'timeout' => 
          array (
            'name' => 'timeout',
            'default' => 
            array (
              'code' => '30.0',
              'attributes' => 
              array (
                'startLine' => 1296,
                'endLine' => 1296,
                'startTokenPos' => 7353,
                'startFilePos' => 46356,
                'endTokenPos' => 7353,
                'endFilePos' => 46359,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'float',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1296,
            'endLine' => 1296,
            'startColumn' => 40,
            'endColumn' => 60,
            'parameterIndex' => 1,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Lytecache\\CacheLock',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Acquires a process-safe lock named $name, polling roughly every
 * 50ms until $timeout seconds elapse. Built on the same atomic add()
 * (Redis "SET NX") semantics as everything else, so only one holder
 * -- across PHP-FPM worker processes sharing the cache file -- can
 * hold a given lock name at once.
 *
 * The lock is also given $timeout as its own TTL, as a safety net: if
 * the holder\'s process dies before calling release(), the lock still
 * expires on its own instead of wedging forever.
 */',
        'startLine' => 1296,
        'endLine' => 1314,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
      'releaseLock' => 
      array (
        'name' => 'releaseLock',
        'parameters' => 
        array (
          'name' => 
          array (
            'name' => 'name',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1321,
            'endLine' => 1321,
            'startColumn' => 33,
            'endColumn' => 44,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'token' => 
          array (
            'name' => 'token',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'string',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 1321,
            'endLine' => 1321,
            'startColumn' => 47,
            'endColumn' => 59,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Releases a lock only if $token still matches what is stored.
 * Called by {@see CacheLock::release()}; not typically called
 * directly.
 */',
        'startLine' => 1321,
        'endLine' => 1333,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\LyteCache',
        'implementingClassName' => 'Lytecache\\LyteCache',
        'currentClassName' => 'Lytecache\\LyteCache',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));