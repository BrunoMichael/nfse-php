<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class TotIbsMunData extends Dto
{
    #[MapFrom('vDifMun')]
    public ?float $valorDiferimentoMunicipal = null;

    #[MapFrom('vIBSMun')]
    public ?float $valorIbsMunicipal = null;
}
