<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class ObraData extends Dto
{
    /**
     * Inscrição imobiliária fiscal da obra.
     */
    #[MapFrom('inscImobFisc')]
    public ?string $inscricaoImobiliariaFiscal = null;

    /**
     * Código da obra.
     */
    #[MapFrom('cObra')]
    public ?string $codigoObra = null;

    /**
     * Endereço da obra.
     */
    #[MapFrom('end')]
    public ?EnderecoData $endereco = null;
}
