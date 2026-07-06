<?php declare(strict_types = 1);

// odsl-/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Eviction.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Lytecache\Eviction
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.5.8-d63215bb7b21f85534028984b5882403fa13205ca165fedec436e0be091acd75',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Lytecache\\Eviction',
        'filename' => '/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Eviction.php',
      ),
    ),
    'namespace' => 'Lytecache',
    'name' => 'Lytecache\\Eviction',
    'shortName' => 'Eviction',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => true,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * Eviction policy applied when a namespace exceeds maxKeys or maxBytes.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 12,
    'endLine' => 30,
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
    ),
    'immediateProperties' => 
    array (
      'name' => 
      array (
        'declaringClassName' => 'Lytecache\\Eviction',
        'implementingClassName' => 'Lytecache\\Eviction',
        'name' => 'name',
        'modifiers' => 2177,
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
        'startLine' => NULL,
        'endLine' => NULL,
        'startColumn' => -1,
        'endColumn' => -1,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      'cases' => 
      array (
        'name' => 'cases',
        'parameters' => 
        array (
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
        'docComment' => NULL,
        'startLine' => NULL,
        'endLine' => NULL,
        'startColumn' => -1,
        'endColumn' => -1,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\Eviction',
        'implementingClassName' => 'Lytecache\\Eviction',
        'currentClassName' => 'Lytecache\\Eviction',
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
    'backingType' => NULL,
    'cases' => 
    array (
      'LRU' => 
      array (
        'name' => 'LRU',
        'value' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** Evict the least-recently-used key first. Default. */',
        'startLine' => 15,
        'endLine' => 15,
        'startColumn' => 5,
        'endColumn' => 13,
      ),
      'TTL' => 
      array (
        'name' => 'TTL',
        'value' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** Evict the soonest-to-expire key first; keys with no TTL are evicted last. */',
        'startLine' => 18,
        'endLine' => 18,
        'startColumn' => 5,
        'endColumn' => 13,
      ),
      'Random' => 
      array (
        'name' => 'Random',
        'value' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/** Evict an arbitrary key. */',
        'startLine' => 21,
        'endLine' => 21,
        'startColumn' => 5,
        'endColumn' => 16,
      ),
      'NoEviction' => 
      array (
        'name' => 'NoEviction',
        'value' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Reject a write that would grow the namespace past the configured
 * limit, throwing {@see CacheFullException},
 * instead of evicting. Updating an existing key is always allowed,
 * since it never grows the dataset.
 */',
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 20,
      ),
    ),
  ),
));