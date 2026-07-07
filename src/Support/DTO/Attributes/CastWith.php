<?php

declare(strict_types=1);

namespace Nfse\Support\DTO\Attributes;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
final class CastWith
{
    public function __construct(
        public string $casterClass,
        public ?string $enumType = null,
        public ?string $itemType = null,
    ) {}
}
