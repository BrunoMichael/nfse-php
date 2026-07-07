<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class AtividadeEventoData extends Dto
{
    /**
     * Nome do evento ou atividade.
     */
    #[MapFrom('xNome')]
    public ?string $nome = null;

    /**
     * Data de início do evento.
     * Formato: AAAA-MM-DD
     */
    #[MapFrom('dtIni')]
    public ?string $dataInicio = null;

    /**
     * Data de fim do evento.
     * Formato: AAAA-MM-DD
     */
    #[MapFrom('dtFim')]
    public ?string $dataFim = null;

    /**
     * Identificador da atividade ou evento.
     */
    #[MapFrom('idAtvEvt')]
    public ?string $idAtividadeEvento = null;

    /**
     * Endereço do evento.
     */
    #[MapFrom('end')]
    public ?EnderecoData $endereco = null;
}
