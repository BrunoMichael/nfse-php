<?php

declare(strict_types=1);

namespace Nfse\Support\DTO\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class MapFrom
{
    public function __construct(
        public string $name,
    ) {}
}
