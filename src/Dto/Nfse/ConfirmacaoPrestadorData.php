<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ConfirmacaoPrestadorData extends Dto
{
    #[MapFrom('xDesc')]
    public ?string $descricao = null;
}
