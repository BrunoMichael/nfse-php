<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ConfirmacaoTomadorData extends Dto
{
    #[MapFrom('xDesc')]
    public ?string $descricao = null;
}
