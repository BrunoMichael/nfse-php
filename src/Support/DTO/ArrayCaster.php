<?php

namespace Nfse\Support\DTO;

class ArrayCaster implements Caster
{
    /** @phpstan-ignore constructor.unusedParameter */
    public function __construct(
        array $types,
        private string $itemType,
    ) {}

    public function cast(mixed $value): mixed
    {
        if (! is_array($value)) {
            return $value;
        }

        return array_map(
            function ($data) {
                if (is_array($data)) {
                    return new $this->itemType($data);
                }

                return $data;
            },
            $value
        );
    }
}
