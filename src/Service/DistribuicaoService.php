<?php

namespace Nfse\Service;

use Nfse\Dto\Http\DistribuicaoDfeResponse;
use Nfse\Enums\TipoNsu;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\NfseContext;

class DistribuicaoService
{
    private AdnClient $adnClient;

    public function __construct(
        NfseContext $context,
        ?AdnClient $adnClient = null,
    ) {
        $this->adnClient = $adnClient ?? new AdnClient($context);
    }

    public function baixarContribuinte(int $nsu, ?string $cnpjConsulta = null, bool $lote = true): DistribuicaoDfeResponse
    {
        return $this->adnClient->baixarDfeContribuinte($nsu, $cnpjConsulta, $lote);
    }

    public function baixarMunicipio(int $nsu, ?TipoNsu $tipoNSU = null, bool $lote = true): DistribuicaoDfeResponse
    {
        return $this->adnClient->baixarDfeMunicipio($nsu, $tipoNSU, $lote);
    }

    public function enviarLote(string $xmlZipB64): array
    {
        return $this->adnClient->enviarLote($xmlZipB64);
    }
}
