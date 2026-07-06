<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class TotIbsMunData extends Dto
{
    #[MapFrom('vDifMun')]
    public ?float $valorDiferimentoMunicipal = null;

    #[MapFrom('vIBSMun')]
    public ?float $valorIbsMunicipal = null;
}
