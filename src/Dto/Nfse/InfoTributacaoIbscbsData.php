<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class InfoTributacaoIbscbsData extends Dto
{
    #[MapFrom('gIBSCBS')]
    public ?SituacaoClassificacaoIbscbsData $situacaoClassificacao = null;
}
