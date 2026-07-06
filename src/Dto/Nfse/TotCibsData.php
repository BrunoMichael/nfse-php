<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class TotCibsData extends Dto
{
    #[MapFrom('vTotNF')]
    public ?float $valorTotalNf = null;

    #[MapFrom('gIBS')]
    public ?TotIbsData $totalIbs = null;

    #[MapFrom('gCBS')]
    public ?TotCbsData $totalCbs = null;
}
