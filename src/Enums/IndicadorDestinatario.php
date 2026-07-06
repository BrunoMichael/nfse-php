<?php

namespace Nfse\Enums;

enum IndicadorDestinatario: string
{
    case TomadorEhDestinatario = '0';
    case DestinatarioDiferente = '1';

    public function label(): string
    {
        return match ($this) {
            self::TomadorEhDestinatario => 'Tomador é o destinatário',
            self::DestinatarioDiferente => 'Destinatário diferente do tomador',
        };
    }
}
