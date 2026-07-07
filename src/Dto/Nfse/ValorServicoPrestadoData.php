<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ValorServicoPrestadoData extends Dto
{
    /**
     * Valor recebido pelo intermediário.
     * Obrigatório se tpEmit = 3.
     */
    #[MapFrom('vReceb')]
    public ?float $valorRecebido = null;

    /**
     * Valor do serviço prestado.
     */
    #[MapFrom('vServ')]
    public ?float $valorServico = null;
}
