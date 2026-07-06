<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class TotCbsData extends Dto
{
    #[MapFrom('vDifCBS')]
    public ?float $valorDiferimentoCbs = null;

    #[MapFrom('vCBS')]
    public ?float $valorCbs = null;
}
