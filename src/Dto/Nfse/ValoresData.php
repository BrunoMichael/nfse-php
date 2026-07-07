<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ValoresData extends Dto
{
    /**
     * Valor do serviço prestado.
     */
    #[MapFrom('vServPrest')]
    public ?ValorServicoPrestadoData $valorServicoPrestado = null;

    /**
     * Descontos condicionados e incondicionados.
     */
    #[MapFrom('vDescCondIncond')]
    public ?DescontoData $desconto = null;

    /**
     * Deduções e reduções da base de cálculo.
     */
    #[MapFrom('vDedRed')]
    public ?DeducaoReducaoData $deducaoReducao = null;

    /**
     * Informações sobre a tributação do serviço.
     */
    #[MapFrom('trib')]
    public ?TributacaoData $tributacao = null;
}
