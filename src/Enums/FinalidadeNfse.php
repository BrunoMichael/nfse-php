<?php

namespace Nfse\Enums;

enum FinalidadeNfse: string
{
    case Regular = '0';

    public function label(): string
    {
        return match ($this) {
            self::Regular => 'NFS-e regular',
        };
    }
}
