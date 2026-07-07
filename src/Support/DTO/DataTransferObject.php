<?php

declare(strict_types=1);

namespace Nfse\Support\DTO;

use Nfse\Support\DTO\Attributes\CastWith;
use Nfse\Support\DTO\Attributes\MapFrom;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

abstract class DataTransferObject
{
    private const MISSING = '__nfse_dto_missing__';

    protected function fill(array $input): void
    {
        $class = new ReflectionClass($this);

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $name = $property->getName();
            $raw = $this->extractRawValue($property, $input);

            if ($raw === self::MISSING) {
                if ($property->hasDefaultValue()) {
                    $this->{$name} = $property->getDefaultValue();

                    continue;
                }

                $this->{$name} = null;

                continue;
            }

            $this->{$name} = $this->castValue($property, $raw);
        }
    }

    private function extractRawValue(ReflectionProperty $property, array $input): mixed
    {
        $candidates = [];

        foreach ($property->getAttributes(MapFrom::class) as $attribute) {
            $candidates[] = $attribute->newInstance()->name;
        }

        $candidates[] = $property->getName();

        foreach ($candidates as $key) {
            if (str_contains($key, '.')) {
                $value = $this->getDotValue($input, $key);

                if ($value !== self::MISSING) {
                    return $value;
                }

                if (array_key_exists($key, $input)) {
                    return $input[$key];
                }

                continue;
            }

            if (array_key_exists($key, $input)) {
                return $input[$key];
            }
        }

        return self::MISSING;
    }

    private function castValue(ReflectionProperty $property, mixed $value): mixed
    {
        if ($value === null) {
            return null;
        }

        foreach ($property->getAttributes(CastWith::class) as $attribute) {
            $castWith = $attribute->newInstance();
            $caster = $this->createCaster($castWith, $property);

            return $caster->cast($value);
        }

        $type = $this->getPropertyNamedType($property);

        if ($type === null) {
            return $value;
        }

        if ($type->isBuiltin()) {
            return $this->castBuiltin($value, $type->getName());
        }

        $typeName = $type->getName();

        if (is_subclass_of($typeName, \BackedEnum::class)) {
            return $this->castBackedEnum($typeName, $value);
        }

        if (is_array($value) && is_subclass_of($typeName, self::class)) {
            return new $typeName($value);
        }

        return $value;
    }

    private function castBuiltin(mixed $value, string $typeName): mixed
    {
        return match ($typeName) {
            'int' => is_int($value) ? $value : (is_numeric($value) ? (int) $value : $value),
            'float' => is_float($value)
                ? $value
                : (is_numeric($value) ? (float) str_replace(',', '.', (string) $value) : $value),
            'bool' => is_bool($value)
                ? $value
                : (filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? (bool) $value),
            'string' => is_string($value) ? $value : (string) $value,
            'array' => is_array($value) ? $value : $value,
            default => $value,
        };
    }

    private function castBackedEnum(string $enumType, mixed $value): mixed
    {
        if ($value instanceof $enumType) {
            return $value;
        }

        if ($value === null) {
            return null;
        }

        $reflection = new \ReflectionEnum($enumType);
        $backingType = $reflection->getBackingType();

        if (
            $reflection->isBacked()
            && $backingType instanceof \ReflectionNamedType
            && $backingType->getName() === 'int'
            && is_string($value)
            && is_numeric($value)
        ) {
            $value = (int) $value;
        }

        return $enumType::tryFrom($value);
    }

    private function createCaster(CastWith $castWith, ReflectionProperty $property): Caster
    {
        $types = [$property->getType()];
        $class = $castWith->casterClass;

        if ($castWith->enumType !== null) {
            return new $class($types, $castWith->enumType);
        }

        if ($castWith->itemType !== null) {
            return new $class($types, $castWith->itemType);
        }

        return new $class($types);
    }

    private function getPropertyNamedType(ReflectionProperty $property): ?ReflectionNamedType
    {
        $type = $property->getType();

        if ($type instanceof ReflectionNamedType) {
            return $type;
        }

        if ($type instanceof ReflectionUnionType) {
            foreach ($type->getTypes() as $unionType) {
                if ($unionType instanceof ReflectionNamedType && $unionType->getName() !== 'null') {
                    return $unionType;
                }
            }
        }

        return null;
    }

    private function getDotValue(array $array, string $key): mixed
    {
        $current = $array;

        foreach (explode('.', $key) as $segment) {
            if (! is_array($current) || ! array_key_exists($segment, $current)) {
                return self::MISSING;
            }

            $current = $current[$segment];
        }

        return $current;
    }
}
