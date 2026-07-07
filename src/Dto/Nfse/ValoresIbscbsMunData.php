<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ValoresIbscbsMunData extends Dto
{
    #[MapFrom('pIBSMun')]
    public ?float $aliquotaIbsMunicipal = null;

    #[MapFrom('pRedAliqMun')]
    public ?float $percentualReducaoAliquotaMunicipal = null;

    #[MapFrom('pAliqEfetMun')]
    public ?float $aliquotaEfetivaMunicipal = null;
}
