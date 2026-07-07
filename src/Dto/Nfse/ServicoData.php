<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ServicoData extends Dto
{
    /**
     * Local da prestação do serviço.
     */
    #[MapFrom('locPrest')]
    public ?LocalPrestacaoData $localPrestacao = null;

    /**
     * Código do serviço prestado.
     */
    #[MapFrom('cServ')]
    public ?CodigoServicoData $codigoServico = null;

    /**
     * Informações de comércio exterior.
     */
    #[MapFrom('comExt')]
    public ?ComercioExteriorData $comercioExterior = null;

    /**
     * Informações da obra.
     */
    #[MapFrom('obra')]
    public ?ObraData $obra = null;

    /**
     * Informações de atividade/evento.
     */
    #[MapFrom('atvEvento')]
    public ?AtividadeEventoData $atividadeEvento = null;

    /**
     * Informações complementares do serviço.
     */
    #[MapFrom('infoCompl')]
    public ?InfoComplData $informacaoComplemento = null;
}
