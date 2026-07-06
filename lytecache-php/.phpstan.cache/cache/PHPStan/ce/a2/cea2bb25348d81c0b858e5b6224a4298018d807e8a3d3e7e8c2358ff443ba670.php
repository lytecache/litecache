<?php declare(strict_types = 1);

// odsl-/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Support/Paths.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Lytecache\Support\Paths
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.5.8-436624794983c3256087d4bf286988944b43ea9e8bf92510a5a8c0c8dd6d3919',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Lytecache\\Support\\Paths',
        'filename' => '/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Support/Paths.php',
      ),
    ),
    'namespace' => 'Lytecache\\Support',
    'name' => 'Lytecache\\Support\\Paths',
    'shortName' => 'Paths',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Default database path resolution, shared by LyteCache and its static
 * defaultPath() accessor.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 11,
    'endLine' => 115,
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
      'ENV_OVERRIDE' => 
      array (
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'name' => 'ENV_OVERRIDE',
        'modifiers' => 4,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'LYTECACHE_PATH\'',
          'attributes' => 
          array (
            'startLine' => 13,
            'endLine' => 13,
            'startTokenPos' => 33,
            'startFilePos' => 223,
            'endTokenPos' => 33,
            'endFilePos' => 238,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 13,
        'endLine' => 13,
        'startColumn' => 5,
        'endColumn' => 50,
      ),
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
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
 * Resolves the default database file location:
 * "<platform cache dir>/lytecache/<project-id>.db", or the
 * LYTECACHE_PATH environment variable if set (after "~" expansion,
 * but not otherwise forced to an absolute path -- a relative
 * LYTECACHE_PATH stays relative, matching the other implementations).
 */',
        'startLine' => 22,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
        'aliasName' => NULL,
      ),
      'platformCacheDir' => 
      array (
        'name' => 'platformCacheDir',
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
        'startLine' => 35,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
        'aliasName' => NULL,
      ),
      'windowsLocalAppData' => 
      array (
        'name' => 'windowsLocalAppData',
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
        'startLine' => 44,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
        'aliasName' => NULL,
      ),
      'xdgCacheHome' => 
      array (
        'name' => 'xdgCacheHome',
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
        'startLine' => 54,
        'endLine' => 62,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
        'aliasName' => NULL,
      ),
      'homeDir' => 
      array (
        'name' => 'homeDir',
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
        'startLine' => 64,
        'endLine' => 78,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => true,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 20,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
        'aliasName' => NULL,
      ),
      'projectId' => 
      array (
        'name' => 'projectId',
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
 * The first 12 hex characters of the SHA-256 hash of the resolved,
 * absolute current working directory -- the same derivation used by
 * the Python, Java, Node.js, and Go implementations of lytecache, so
 * a process in any of those languages started from the same
 * directory resolves to the same file.
 */',
        'startLine' => 87,
        'endLine' => 100,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
        'aliasName' => NULL,
      ),
      'expandHome' => 
      array (
        'name' => 'expandHome',
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
            'startLine' => 103,
            'endLine' => 103,
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
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => '/** Expands a leading "~" or "~/" to the user\'s home directory. */',
        'startLine' => 103,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Lytecache\\Support',
        'declaringClassName' => 'Lytecache\\Support\\Paths',
        'implementingClassName' => 'Lytecache\\Support\\Paths',
        'currentClassName' => 'Lytecache\\Support\\Paths',
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