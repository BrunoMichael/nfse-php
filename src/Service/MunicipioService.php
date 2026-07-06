<?php

namespace Nfse\Service;

use Nfse\Enums\TipoNsu;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\Client\CncClient;
use Nfse\Http\NfseContext;

/**
 * @deprecated Use {@see DistribuicaoService}, {@see ParametrosService} e {@see CadastroService}.
 */
class MunicipioService
{
    private AdnClient $adnClient;

    private DistribuicaoService $distribuicaoService;

    private ParametrosService $parametrosService;

    private CadastroService $cadastroService;

    public function __construct(
        NfseContext $context,
        ?AdnClient $adnClient = null,
        ?CncClient $cncClient = null,
    ) {
        $this->adnClient = $adnClient ?? new AdnClient($context);
        $cncClient ??= new CncClient($context);

        $this->distribuicaoService = new DistribuicaoService($context, $this->adnClient);
        $this->parametrosService = new ParametrosService($context, $this->adnClient);
        $this->cadastroService = new CadastroService($context, $cncClient);
    }

    public function baixarDfe(int $nsu, ?TipoNsu $tipoNSU = null, bool $lote = true): \Nfse\Dto\Http\DistribuicaoDfeResponse
    {
        return $this->distribuicaoService->baixarMunicipio($nsu, $tipoNSU, $lote);
    }

    /**
     * @deprecated A API oficial do ambiente nacional para geração do Documento Auxiliar
     * da Nota Fiscal de Serviços Eletrônica (DANFSe) será descontinuada em 1º de julho de 2026. A emissão
     * passará a ser responsabilidade dos sistemas emissores, ERPs e softwares das próprias empresas.
     * Fim da API de Geração: a interface oficial do governo que gerava o DANFSe será desligada.
     * Responsabilidade do Emissor: ERPs, softwares de gestão e plataformas de contabilidade precisarão
     * gerar o DANFSe internamente e adequar seus layouts. Novo Layout: o documento agora possui um formato
     * padrão obrigatório em folha A4, exigência de QR Code e inclusão de campos para IBS e CBS.
     * Nota técnica: https://www.gov.br/nfse/pt-br/biblioteca/documentacao-tecnica/rtc/nt-008-se-cgnfse-danfse-20260505.pdf
     */
    public function downloadDanfse(string $chaveAcesso): string
    {
        return $this->adnClient->obterDanfse($chaveAcesso);
    }

    public function enviarLote(string $xmlZipB64): array
    {
        return $this->distribuicaoService->enviarLote($xmlZipB64);
    }

    public function consultarParametrosConvenio(string $codigoMunicipio): \Nfse\Dto\Http\ResultadoConsultaConfiguracoesConvenioResponse
    {
        return $this->parametrosService->consultarConvenio($codigoMunicipio);
    }

    public function consultarAliquota(string $codigoMunicipio, string $codigoServico, string $competencia): \Nfse\Dto\Http\ResultadoConsultaAliquotasResponse
    {
        return $this->parametrosService->consultarAliquota($codigoMunicipio, $codigoServico, $competencia);
    }

    public function consultarHistoricoAliquotas(string $codigoMunicipio, string $codigoServico): \Nfse\Dto\Http\ResultadoConsultaAliquotasResponse
    {
        return $this->parametrosService->consultarHistoricoAliquotas($codigoMunicipio, $codigoServico);
    }

    public function consultarBeneficio(string $codigoMunicipio, string $numeroBeneficio, string $competencia): array
    {
        return $this->parametrosService->consultarBeneficio($codigoMunicipio, $numeroBeneficio, $competencia);
    }

    public function consultarRegimesEspeciais(string $codigoMunicipio, string $codigoServico, string $competencia): array
    {
        return $this->parametrosService->consultarRegimesEspeciais($codigoMunicipio, $codigoServico, $competencia);
    }

    public function consultarRetencoes(string $codigoMunicipio, string $competencia): array
    {
        return $this->parametrosService->consultarRetencoes($codigoMunicipio, $competencia);
    }

    public function consultarContribuinte(string $cpfCnpj): array
    {
        return $this->cadastroService->consultarContribuinte($cpfCnpj);
    }

    public function baixarAlteracoesCadastro(int $nsu): array
    {
        return $this->cadastroService->baixarAlteracoes($nsu);
    }

    public function atualizarContribuinte(array $dados): array
    {
        return $this->cadastroService->atualizarContribuinte($dados);
    }
}
