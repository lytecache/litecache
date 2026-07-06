<?php declare(strict_types = 1);

// odsl-/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Bytes.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Lytecache\Bytes
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.5.8-6845c5272dde04e53ed96d10363526767911a8a14e19e2e128a8948d2f345c28',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Lytecache\\Bytes',
        'filename' => '/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Bytes.php',
      ),
    ),
    'namespace' => 'Lytecache',
    'name' => 'Lytecache\\Bytes',
    'shortName' => 'Bytes',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * Wraps a raw binary string so it is stored as type code 0 (bytes) instead
 * of type code 1 (UTF-8 string). PHP strings are byte sequences with no
 * built-in text/binary distinction, so this wrapper is how a caller tells
 * lytecache "this is raw binary data, not text":
 *
 *     $cache->set(\'blob\', new Bytes($rawBinaryString));
 *     $raw = $cache->get(\'blob\'); // returns a plain string of the raw bytes
 *
 * On read, a code-0 value is returned as a plain PHP string (not
 * re-wrapped in Bytes) -- the wrapper only matters for picking the type
 * code at write time.
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 20,
    'endLine' => 23,
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
      'value' => 
      array (
        'declaringClassName' => 'Lytecache\\Bytes',
        'implementingClassName' => 'Lytecache\\Bytes',
        'name' => 'value',
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
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 33,
        'endColumn' => 61,
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
          'value' => 
          array (
            'name' => 'value',
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
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 22,
            'endLine' => 22,
            'startColumn' => 33,
            'endColumn' => 61,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 65,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache',
        'declaringClassName' => 'Lytecache\\Bytes',
        'implementingClassName' => 'Lytecache\\Bytes',
        'currentClassName' => 'Lytecache\\Bytes',
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