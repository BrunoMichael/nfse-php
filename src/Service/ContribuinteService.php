<?php

namespace Nfse\Service;

use Nfse\Dto\Nfse\DpsData;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Dto\Nfse\PedRegEventoData;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\Client\SefinClient;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\NfseContext;
use Nfse\Signer\Certificate;
use Nfse\Signer\SignerInterface;
use Nfse\Signer\XmlSigner;

/**
 * @deprecated Use {@see EmissaoService}, {@see EventosService}, {@see DistribuicaoService} e {@see ParametrosService}.
 */
class ContribuinteService
{
    private SefinNacionalInterface $sefinClient;

    private AdnClient $adnClient;

    private EmissaoService $emissaoService;

    private EventosService $eventosService;

    private DistribuicaoService $distribuicaoService;

    private ParametrosService $parametrosService;

    public function __construct(
        private NfseContext $context,
        ?SefinNacionalInterface $sefinClient = null,
        ?AdnClient $adnClient = null,
    ) {
        $this->sefinClient = $sefinClient ?? new SefinClient($context);
        $this->adnClient = $adnClient ?? new AdnClient($context);

        $signerFactory = fn (Certificate $certificate) => $this->createSigner($certificate);

        $this->emissaoService = new EmissaoService($context, $this->sefinClient, $signerFactory);
        $this->eventosService = new EventosService($context, $this->sefinClient, $this->adnClient, $signerFactory);
        $this->distribuicaoService = new DistribuicaoService($context, $this->adnClient);
        $this->parametrosService = new ParametrosService($context, $this->adnClient);
    }

    public function emitir(DpsData $dps): NfseData
    {
        return $this->emissaoService->emitir($dps);
    }

    public function consultar(string $chave): ?NfseData
    {
        return $this->emissaoService->consultar($chave);
    }

    public function consultarDps(string $idDps): \Nfse\Dto\Http\ConsultaDpsResponse
    {
        return $this->emissaoService->consultarDps($idDps);
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

    public function verificarDps(string $idDps): bool
    {
        return $this->emissaoService->verificarDps($idDps);
    }

    public function registrarEvento(string $chaveAcesso, string $eventoXmlGZipB64): \Nfse\Dto\Http\RegistroEventoResponse
    {
        return $this->eventosService->registrarPayload($chaveAcesso, $eventoXmlGZipB64);
    }

    public function registrarEventoData(PedRegEventoData $evento): \Nfse\Dto\Http\RegistroEventoResponse
    {
        return $this->eventosService->registrar($evento);
    }

    public function cancelar(PedRegEventoData $evento): \Nfse\Dto\Http\RegistroEventoResponse
    {
        return $this->eventosService->cancelar($evento);
    }

    public function consultarEvento(string $chaveAcesso, int $tipoEvento, int $numSeqEvento): \Nfse\Dto\Http\RegistroEventoResponse
    {
        return $this->eventosService->consultar($chaveAcesso, $tipoEvento, $numSeqEvento);
    }

    public function listarEventos(string $chaveAcesso, ?int $tipoEvento = null): array
    {
        return $this->eventosService->listar($chaveAcesso, $tipoEvento);
    }

    public function baixarDfe(int $nsu, ?string $cnpjConsulta = null, bool $lote = true): \Nfse\Dto\Http\DistribuicaoDfeResponse
    {
        return $this->distribuicaoService->baixarContribuinte($nsu, $cnpjConsulta, $lote);
    }

    public function consultarEventos(string $chaveAcesso): array
    {
        return $this->eventosService->listarAdn($chaveAcesso);
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

    protected function createSigner(Certificate $certificate): SignerInterface
    {
        return new XmlSigner($certificate);
    }
}
