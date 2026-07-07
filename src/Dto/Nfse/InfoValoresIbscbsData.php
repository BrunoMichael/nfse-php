<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class InfoValoresIbscbsData extends Dto
{
    #[MapFrom('trib')]
    public ?InfoTributacaoIbscbsData $tributacao = null;
}
