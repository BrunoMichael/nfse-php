<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class TotIbsUfData extends Dto
{
    #[MapFrom('vDifUF')]
    public ?float $valorDiferimentoUf = null;

    #[MapFrom('vIBSUF')]
    public ?float $valorIbsUf = null;
}
