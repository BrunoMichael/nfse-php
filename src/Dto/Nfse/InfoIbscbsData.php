<?php

namespace Nfse\Dto\Nfse;

use Nfse\Dto\Dto;
use Nfse\Enums\FinalidadeNfse;
use Nfse\Enums\IndicadorConsumoFinal;
use Nfse\Enums\IndicadorDestinatario;
use Nfse\Support\DTO\EnumCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\MapFrom;

class InfoIbscbsData extends Dto
{
    #[MapFrom('finNFSe'), CastWith(EnumCaster::class, enumType: FinalidadeNfse::class)]
    public ?FinalidadeNfse $finalidade = null;

    #[MapFrom('indFinal'), CastWith(EnumCaster::class, enumType: IndicadorConsumoFinal::class)]
    public ?IndicadorConsumoFinal $indicadorConsumoFinal = null;

    #[MapFrom('cIndOp')]
    public ?string $codigoIndicadorOperacao = null;

    #[MapFrom('tpOper')]
    public ?string $tipoOperacao = null;

    #[MapFrom('indDest'), CastWith(EnumCaster::class, enumType: IndicadorDestinatario::class)]
    public ?IndicadorDestinatario $indicadorDestinatario = null;

    #[MapFrom('valores')]
    public ?InfoValoresIbscbsData $valores = null;
}
