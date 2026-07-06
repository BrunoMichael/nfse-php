<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Spatie\DataTransferObject\Attributes\MapFrom;

class InfoTributacaoIbscbsData extends Dto
{
    #[MapFrom('gIBSCBS')]
    public ?SituacaoClassificacaoIbscbsData $situacaoClassificacao = null;
}
