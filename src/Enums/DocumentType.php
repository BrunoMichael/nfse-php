<?php

namespace Nfse\Enums;

enum DocumentType: string
{
    case Dps = 'DPS';
    case Nfse = 'NFSe';
    case PedRegEvento = 'pedRegEvento';
    case Evento = 'evento';
}
