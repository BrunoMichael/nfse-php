<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ValoresIbscbsNfseData extends Dto
{
    #[MapFrom('vBC')]
    public ?float $baseCalculo = null;

    #[MapFrom('vCalcReeRepRes')]
    public ?float $valorReeRepRes = null;

    #[MapFrom('uf')]
    public ?ValoresIbscbsUfData $uf = null;

    #[MapFrom('mun')]
    public ?ValoresIbscbsMunData $municipio = null;

    #[MapFrom('fed')]
    public ?ValoresIbscbsFedData $federal = null;
}
