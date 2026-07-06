<?php declare(strict_types = 1);

// odsl-/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Laravel/Console/MaintainCommand.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Lytecache\Laravel\Console\MaintainCommand
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.70.0.3-8.5.8-f0d90954d33139aa4e5186790997b50e6a57cfa67a72cde1d7e55049b1900cbd',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'filename' => '/Users/silemobayo/Documents/PERSONAL-PROJECT/litecache/lytecache-php/src/Laravel/Console/MaintainCommand.php',
      ),
    ),
    'namespace' => 'Lytecache\\Laravel\\Console',
    'name' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
    'shortName' => 'MaintainCommand',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 32,
    'docComment' => '/**
 * php artisan lytecache:maintain
 *
 * Runs LyteCache::maintain(): removes expired keys and enforces eviction
 * limits. PHP has no background threads, so this -- run on Laravel\'s
 * scheduler -- is what replaces the background sweeper the other
 * language implementations run automatically:
 *
 *     $schedule->command(\'lytecache:maintain\')->everyMinute();
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 20,
    'endLine' => 34,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Console\\Command',
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
      'signature' => 
      array (
        'declaringClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'implementingClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'name' => 'signature',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'lytecache:maintain\'',
          'attributes' => 
          array (
            'startLine' => 22,
            'endLine' => 22,
            'startTokenPos' => 45,
            'startFilePos' => 571,
            'endTokenPos' => 45,
            'endFilePos' => 590,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 22,
        'endLine' => 22,
        'startColumn' => 5,
        'endColumn' => 48,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'description' => 
      array (
        'declaringClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'implementingClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'name' => 'description',
        'modifiers' => 2,
        'type' => NULL,
        'default' => 
        array (
          'code' => '\'Remove expired lytecache keys and enforce eviction limits\'',
          'attributes' => 
          array (
            'startLine' => 24,
            'endLine' => 24,
            'startTokenPos' => 54,
            'startFilePos' => 623,
            'endTokenPos' => 54,
            'endFilePos' => 681,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 24,
        'endLine' => 24,
        'startColumn' => 5,
        'endColumn' => 89,
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
      'handle' => 
      array (
        'name' => 'handle',
        'parameters' => 
        array (
          'cache' => 
          array (
            'name' => 'cache',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Lytecache\\LyteCache',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 26,
            'endLine' => 26,
            'startColumn' => 28,
            'endColumn' => 43,
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
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 26,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Lytecache\\Laravel\\Console',
        'declaringClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'implementingClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
        'currentClassName' => 'Lytecache\\Laravel\\Console\\MaintainCommand',
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