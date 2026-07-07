<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class IbscbsNfseData extends Dto
{
    #[MapFrom('cLocalidadeIncid')]
    public ?string $codigoLocalidadeIncidencia = null;

    #[MapFrom('xLocalidadeIncid')]
    public ?string $nomeLocalidadeIncidencia = null;

    #[MapFrom('pRedutor')]
    public ?float $percentualRedutor = null;

    #[MapFrom('valores')]
    public ?ValoresIbscbsNfseData $valores = null;

    #[MapFrom('totCIBS')]
    public ?TotCibsData $totalizadores = null;
}
