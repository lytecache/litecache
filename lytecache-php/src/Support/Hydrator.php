<?php

declare(strict_types=1);

namespace Lytecache\Support;

use Lytecache\Exceptions\SerializationException;

/**
 * Rehydrates a decoded JSON value onto a class, for
 * LyteCache::get($key, class: SomeClass::class).
 */
final class Hydrator
{
    /**
     * @param  class-string  $class
     */
    public static function hydrate(mixed $decoded, string $class): mixed
    {
        if (enum_exists($class)) {
            return self::hydrateEnum($decoded, $class);
        }

        if (is_a($class, \DateTimeInterface::class, true) && is_string($decoded)) {
            return self::hydrateDateTime($decoded, $class);
        }

        if (! is_array($decoded)) {
            throw new SerializationException("lytecache: cannot hydrate {$class} from a non-object JSON value");
        }

        if (! class_exists($class)) {
            throw new SerializationException("lytecache: class {$class} does not exist");
        }

        // class_exists() is checked above, so ReflectionClass cannot throw
        // ReflectionException here (that only happens for a nonexistent class).
        $reflection = new \ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        if ($constructor !== null && $constructor->getNumberOfParameters() > 0) {
            return self::hydrateViaConstructor($decoded, $reflection, $constructor);
        }

        return self::hydrateViaProperties($decoded, $reflection);
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @param  \ReflectionClass<object>  $reflection
     */
    private static function hydrateViaConstructor(
        array $decoded,
        \ReflectionClass $reflection,
        \ReflectionMethod $constructor
    ): object {
        $args = [];

        foreach ($constructor->getParameters() as $param) {
            $name = $param->getName();

            if (array_key_exists($name, $decoded)) {
                $args[$name] = self::coerceValueForType($decoded[$name], $param->getType());
            } elseif ($param->isDefaultValueAvailable()) {
                $args[$name] = $param->getDefaultValue();
            } elseif ($param->allowsNull()) {
                $args[$name] = null;
            } else {
                $class = $reflection->getName();

                throw new SerializationException(
                    "lytecache: cannot hydrate {$class}: missing required property \"{$name}\""
                );
            }
        }

        try {
            return $reflection->newInstance(...$args);
        } catch (\Throwable $e) {
            throw new SerializationException(
                "lytecache: cannot hydrate {$reflection->getName()}: ".$e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * @param  array<string, mixed>  $decoded
     * @param  \ReflectionClass<object>  $reflection
     */
    private static function hydrateViaProperties(array $decoded, \ReflectionClass $reflection): object
    {
        try {
            $instance = $reflection->newInstanceWithoutConstructor();
        } catch (\ReflectionException $e) {
            throw new SerializationException(
                "lytecache: cannot hydrate {$reflection->getName()}: ".$e->getMessage(),
                previous: $e
            );
        }

        foreach ($decoded as $key => $value) {
            $key = (string) $key;

            if (! $reflection->hasProperty($key)) {
                continue;
            }

            $property = $reflection->getProperty($key);

            if (! $property->isPublic()) {
                continue;
            }

            $property->setValue($instance, self::coerceValueForType($value, $property->getType()));
        }

        return $instance;
    }

    private static function coerceValueForType(mixed $value, ?\ReflectionType $type): mixed
    {
        if (! $type instanceof \ReflectionNamedType || $type->isBuiltin()) {
            return $value;
        }

        $typeName = $type->getName();

        if (is_string($value) && is_a($typeName, \DateTimeInterface::class, true)) {
            return self::hydrateDateTime($value, $typeName);
        }

        if (is_array($value) && (class_exists($typeName) || enum_exists($typeName))) {
            return self::hydrate($value, $typeName);
        }

        if (enum_exists($typeName)) {
            return self::hydrateEnum($value, $typeName);
        }

        return $value;
    }

    /**
     * @param  class-string  $class
     */
    private static function hydrateEnum(mixed $decoded, string $class): \UnitEnum
    {
        if (! is_subclass_of($class, \BackedEnum::class)) {
            throw new SerializationException(
                "lytecache: cannot hydrate a pure (non-backed) enum {$class} -- there is no portable value to match against"
            );
        }

        if (! is_int($decoded) && ! is_string($decoded)) {
            throw new SerializationException("lytecache: cannot hydrate enum {$class} from a non-scalar value");
        }

        /** @var class-string<\BackedEnum> $class */
        try {
            return $class::from($decoded);
        } catch (\ValueError $e) {
            throw new SerializationException("lytecache: cannot hydrate enum {$class}: ".$e->getMessage(), previous: $e);
        }
    }

    /**
     * @param  class-string  $class
     */
    private static function hydrateDateTime(string $value, string $class): \DateTimeInterface
    {
        $concreteClass = $class === \DateTimeInterface::class ? \DateTimeImmutable::class : $class;

        try {
            /** @var \DateTimeInterface $instance */
            $instance = new $concreteClass($value);

            return $instance;
        } catch (\Throwable $e) {
            throw new SerializationException(
                "lytecache: cannot parse \"{$value}\" as {$class}: ".$e->getMessage(),
                previous: $e
            );
        }
    }
}
