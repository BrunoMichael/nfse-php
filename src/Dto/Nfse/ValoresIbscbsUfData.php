<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class ValoresIbscbsUfData extends Dto
{
    #[MapFrom('pIBSUF')]
    public ?float $aliquotaIbsUf = null;

    #[MapFrom('pRedAliqUF')]
    public ?float $percentualReducaoAliquotaUf = null;

    #[MapFrom('pAliqEfetUF')]
    public ?float $aliquotaEfetivaUf = null;
}
