<?php

namespace Nfse\Danfse;

/**
 * View model plano para renderização do DANFSe (NT-008).
 */
final class DanfseViewModel
{
    public string $chaveAcesso = '';

    public string $urlConsulta = '';

    public string $qrCodeDataUri = '';

    public bool $exibirAvisoHomologacao = false;

    public ?DanfseWatermark $marcaDagua = null;

    public string $tituloDocumento = 'DANFSe v2.0';

    public string $municipioEmissor = '-';

    public string $ambienteGerador = '-';

    public string $tipoAmbiente = '-';

    public string $numeroNfse = '-';

    public string $competencia = '-';

    public string $dataHoraEmissaoNfse = '-';

    public string $numeroDps = '-';

    public string $serieDps = '-';

    public string $dataHoraEmissaoDps = '-';

    public string $emitenteNfse = '-';

    public string $situacaoNfse = '-';

    public string $finalidade = '-';

    public string $prestadorDocumento = '-';

    public string $prestadorInscricaoMunicipal = '-';

    public string $prestadorTelefone = '-';

    public string $prestadorNome = '-';

    public string $prestadorMunicipioUf = '-';

    public string $prestadorCodigoIbgeCep = '-';

    public string $prestadorEndereco = '-';

    public string $prestadorEmail = '-';

    public string $prestadorSimplesNacional = '-';

    public string $prestadorRegimeApuracaoSn = '-';

    public bool $tomadorIdentificado = false;

    public string $tomadorDocumento = '-';

    public string $tomadorInscricaoMunicipal = '-';

    public string $tomadorTelefone = '-';

    public string $tomadorNome = '-';

    public string $tomadorMunicipioUf = '-';

    public string $tomadorCodigoIbgeCep = '-';

    public string $tomadorEndereco = '-';

    public string $tomadorEmail = '-';

    public bool $destinatarioIdentificado = false;

    public bool $destinatarioEhTomador = false;

    public string $destinatarioDocumento = '-';

    public string $destinatarioTelefone = '-';

    public string $destinatarioNome = '-';

    public string $destinatarioMunicipioUf = '-';

    public string $destinatarioCodigoIbgeCep = '-';

    public string $destinatarioEndereco = '-';

    public string $destinatarioEmail = '-';

    public bool $intermediarioIdentificado = false;

    public string $intermediarioDocumento = '-';

    public string $intermediarioInscricaoMunicipal = '-';

    public string $intermediarioTelefone = '-';

    public string $intermediarioNome = '-';

    public string $intermediarioMunicipioUf = '-';

    public string $intermediarioCodigoIbgeCep = '-';

    public string $intermediarioEndereco = '-';

    public string $intermediarioEmail = '-';

    public string $codigoTributacao = '-';

    public string $codigoNbs = '-';

    public string $localPrestacao = '-';

    public string $descricaoTributacao = '-';

    public string $descricaoServico = '-';

    public bool $exibirTributacaoIssqn = true;

    public string $tipoTributacaoIssqn = '-';

    public string $municipioIncidenciaIssqn = '-';

    public string $regimeEspecialIssqn = '-';

    public string $tipoImunidadeIssqn = '-';

    public string $suspensaoIssqn = '-';

    public string $numeroProcessoSuspensao = '-';

    public string $beneficioMunicipal = '-';

    public string $calculoBm = '-';

    public string $totalDeducoes = '-';

    public string $descontoIncondicionadoIssqn = '-';

    public string $baseCalculoIssqn = '-';

    public string $aliquotaIssqn = '-';

    public string $retencaoIssqn = '-';

    public string $valorIssqn = '-';

    public string $irrf = '-';

    public string $contribuicaoPrevidenciaria = '-';

    public string $contribuicoesSociaisRetidas = '-';

    public string $pisProprio = '-';

    public string $cofinsProprio = '-';

    public string $descricaoContribuicoesRetidas = '-';

    public string $cstClassTrib = '-';

    public bool $exibirTributacaoIbsCbs = false;

    public string $indicadorOperacaoIbs = '-';

    public string $exclusoesReducoesBc = '-';

    public string $baseCalculoIbsCbs = '-';

    public string $reducaoAliquotaIbsCbs = '-';

    public string $aliquotaIbs = '-';

    public string $aliquotaEfetivaIbsMunicipal = '-';

    public string $valorIbsMunicipal = '-';

    public string $aliquotaEfetivaIbsEstadual = '-';

    public string $valorIbsEstadual = '-';

    public string $valorTotalIbs = '-';

    public string $aliquotaCbs = '-';

    public string $aliquotaEfetivaCbs = '-';

    public string $valorTotalCbs = '-';

    public string $valorOperacao = '-';

    public string $descontoIncondicionado = '-';

    public string $descontoCondicionado = '-';

    public string $totalRetencoes = '-';

    public string $valorLiquidoNfse = '-';

    public string $totalIbsCbs = '-';

    public string $valorLiquidoComIbsCbs = '-';

    public string $informacoesComplementares = '-';
}
