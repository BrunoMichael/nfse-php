<?php

namespace Nfse\Enums;

enum IndicadorConsumoFinal: string
{
    case Nao = '0';
    case Sim = '1';

    public function label(): string
    {
        return match ($this) {
            self::Nao => 'Não',
            self::Sim => 'Sim',
        };
    }
}
