<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class TotCbsData extends Dto
{
    #[MapFrom('vDifCBS')]
    public ?float $valorDiferimentoCbs = null;

    #[MapFrom('vCBS')]
    public ?float $valorCbs = null;
}
