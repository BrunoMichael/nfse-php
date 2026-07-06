<?php

namespace Nfse\Service;

use Nfse\Dto\Http\ResultadoConsultaAliquotasResponse;
use Nfse\Dto\Http\ResultadoConsultaConfiguracoesConvenioResponse;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\NfseContext;

class ParametrosService
{
    private AdnClient $adnClient;

    public function __construct(
        NfseContext $context,
        ?AdnClient $adnClient = null,
    ) {
        $this->adnClient = $adnClient ?? new AdnClient($context);
    }

    public function consultarConvenio(string $codigoMunicipio): ResultadoConsultaConfiguracoesConvenioResponse
    {
        return $this->adnClient->consultarParametrosConvenio($codigoMunicipio);
    }

    public function consultarAliquota(string $codigoMunicipio, string $codigoServico, string $competencia): ResultadoConsultaAliquotasResponse
    {
        return $this->adnClient->consultarAliquota($codigoMunicipio, $codigoServico, $competencia);
    }

    public function consultarHistoricoAliquotas(string $codigoMunicipio, string $codigoServico): ResultadoConsultaAliquotasResponse
    {
        return $this->adnClient->consultarHistoricoAliquotas($codigoMunicipio, $codigoServico);
    }

    public function consultarBeneficio(string $codigoMunicipio, string $numeroBeneficio, string $competencia): array
    {
        return $this->adnClient->consultarBeneficio($codigoMunicipio, $numeroBeneficio, $competencia);
    }

    public function consultarRegimesEspeciais(string $codigoMunicipio, string $codigoServico, string $competencia): array
    {
        return $this->adnClient->consultarRegimesEspeciais($codigoMunicipio, $codigoServico, $competencia);
    }

    public function consultarRetencoes(string $codigoMunicipio, string $competencia): array
    {
        return $this->adnClient->consultarRetencoes($codigoMunicipio, $competencia);
    }
}
