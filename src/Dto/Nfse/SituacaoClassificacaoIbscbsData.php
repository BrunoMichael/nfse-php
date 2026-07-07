<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class SituacaoClassificacaoIbscbsData extends Dto
{
    #[MapFrom('CST')]
    public ?string $codigoSituacaoTributaria = null;

    #[MapFrom('cClassTrib')]
    public ?string $codigoClassificacaoTributaria = null;

    #[MapFrom('cCredPres')]
    public ?string $codigoCreditoPresumido = null;
}
