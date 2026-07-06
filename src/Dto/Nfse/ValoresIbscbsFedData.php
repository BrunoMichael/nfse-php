<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class ValoresIbscbsFedData extends Dto
{
    #[MapFrom('pCBS')]
    public ?float $aliquotaCbs = null;

    #[MapFrom('pRedAliqCBS')]
    public ?float $percentualReducaoAliquotaCbs = null;

    #[MapFrom('pAliqEfetCBS')]
    public ?float $aliquotaEfetivaCbs = null;
}
