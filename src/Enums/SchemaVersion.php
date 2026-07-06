<?php

namespace Nfse\Enums;

enum SchemaVersion: string
{
    case V1_00 = '1.00';
    case V1_01 = '1.01';
    case V1_02 = '1.02';
    case V2_00 = '2.00';

    public static function fromString(?string $version): self
    {
        return self::tryFrom((string) $version) ?? self::V1_01;
    }
}
