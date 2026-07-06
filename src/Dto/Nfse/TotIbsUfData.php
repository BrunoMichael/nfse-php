<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class TotIbsUfData extends Dto
{
    #[MapFrom('vDifUF')]
    public ?float $valorDiferimentoUf = null;

    #[MapFrom('vIBSUF')]
    public ?float $valorIbsUf = null;
}
