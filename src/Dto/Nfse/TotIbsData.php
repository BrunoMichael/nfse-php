<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class TotIbsData extends Dto
{
    #[MapFrom('vIBSTot')]
    public ?float $valorTotalIbs = null;

    #[MapFrom('gIBSUFTot')]
    public ?TotIbsUfData $totalUf = null;

    #[MapFrom('gIBSMunTot')]
    public ?TotIbsMunData $totalMunicipal = null;
}
