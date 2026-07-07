<?php

namespace Nfse\Dto;

use Nfse\Support\DTO\Attributes\MapFrom;
use Nfse\Support\DTO\DataTransferObject;
use ReflectionClass;
use ReflectionProperty;

abstract class Dto extends DataTransferObject
{
    public function __construct(...$args)
    {
        if (isset($args[0]) && is_array($args[0])) {
            $input = $args[0];
        } else {
            $input = $args;
        }

        $input = $this->normalizeInput($input);
        $this->fill($input);
    }

    protected function normalizeInput(array $input): array
    {
        $class = new ReflectionClass($this);

        foreach ($class->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            $propName = $property->getName();

            if (array_key_exists($propName, $input)) {
                $attributes = $property->getAttributes(MapFrom::class);

                if (count($attributes) > 0) {
                    $mapFrom = $attributes[0]->newInstance();
                    $mappedName = $mapFrom->name;

                    if (str_contains($mappedName, '.')) {
                        if (! $this->hasDotNotation($input, $mappedName)) {
                            $this->setDotNotation($input, $mappedName, $input[$propName]);
                        }
                    } elseif (! array_key_exists($mappedName, $input)) {
                        $input[$mappedName] = $input[$propName];
                    }
                }
            }
        }

        return $input;
    }

    protected function hasDotNotation(array $array, string $key): bool
    {
        $keys = explode('.', $key);
        $current = $array;

        foreach ($keys as $k) {
            if (! is_array($current) || ! array_key_exists($k, $current)) {
                return false;
            }
            $current = $current[$k];
        }

        return true;
    }

    protected function setDotNotation(array &$array, string $key, mixed $value): void
    {
        $keys = explode('.', $key);
        $current = &$array;

        foreach ($keys as $i => $k) {
            if ($i === count($keys) - 1) {
                $current[$k] = $value;
            } else {
                if (! isset($current[$k]) || ! is_array($current[$k])) {
                    $current[$k] = [];
                }
                $current = &$current[$k];
            }
        }
    }
}
