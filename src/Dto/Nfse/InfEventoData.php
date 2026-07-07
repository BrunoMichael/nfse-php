<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Support\DTO\Attributes\MapFrom;

class InfEventoData extends Dto
{
    #[MapFrom('@attributes.Id')]
    public ?string $id = null;

    #[MapFrom('verAplic')]
    public ?string $versaoAplicativo = null;

    #[MapFrom('ambGer')]
    public ?int $ambiente = null;

    #[MapFrom('nSeqEvento')]
    public ?int $numeroSequencialEvento = null;

    #[MapFrom('dhProc')]
    public ?string $dataHoraProcessamento = null;

    #[MapFrom('nDFe')]
    public ?string $numeroDfe = null;

    #[MapFrom('pedRegEvento')]
    public ?PedRegEventoData $pedRegEvento = null;
}
