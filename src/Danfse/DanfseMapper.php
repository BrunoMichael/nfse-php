<?php

namespace Nfse\Danfse;

use Nfse\Contract\QrCodeGeneratorInterface;
use Nfse\Danfse\Support\CompositeQrCodeGenerator;
use Nfse\Dto\Nfse\InfDpsData;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Dto\Nfse\TomadorData;
use Nfse\Dto\Nfse\TributacaoData;
use Nfse\Enums\CodigoStatus;
use Nfse\Enums\EmitenteDPS;
use Nfse\Enums\OpcaoSimplesNacional;
use Nfse\Enums\RegimeApuracaoSN;
use Nfse\Enums\RegimeEspecialTributacao;
use Nfse\Enums\TipoAmbiente;
use Nfse\Enums\TributacaoIssqn;
use Nfse\Enums\TipoRetencaoIssqn;
use InvalidArgumentException;

final class DanfseMapper
{
    public function __construct(
        private ?QrCodeGeneratorInterface $qrCodeGenerator = null,
    ) {
        $this->qrCodeGenerator ??= new CompositeQrCodeGenerator;
    }

    public function map(NfseData $nfse, ?DanfseWatermark $watermark = null): DanfseViewModel
    {
        $inf = $nfse->infNfse;
        if ($inf === null) {
            throw new InvalidArgumentException('NfseData sem infNfse não pode gerar DANFSe.');
        }

        $infDps = $inf->dps?->infDps;
        $view = new DanfseViewModel;

        $chave = DanfseFormatter::chaveFromId($inf->id);
        $view->chaveAcesso = DanfseFormatter::dash($chave);
        $view->urlConsulta = 'https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave='.$chave;
        $view->qrCodeDataUri = $this->qrCodeGenerator->generateDataUri($view->urlConsulta);

        $view->exibirAvisoHomologacao = $infDps?->tipoAmbiente === TipoAmbiente::Homologacao;
        $view->marcaDagua = $watermark ?? $this->detectWatermark($inf->codigoStatus);

        $view->municipioEmissor = DanfseFormatter::dash($inf->localEmissao);
        $view->prefeituraNome = $inf->localEmissao
            ? 'PREFEITURA MUNICIPAL DE '.mb_strtoupper($inf->localEmissao)
            : '-';
        $view->ambienteGerador = $inf->ambienteGerador?->label() ?? '-';
        $view->tipoAmbiente = $infDps?->tipoAmbiente?->label() ?? '-';

        $view->numeroNfse = DanfseFormatter::dash($inf->numeroNfse);
        $view->competencia = DanfseFormatter::date($infDps?->dataCompetencia);
        $view->dataHoraEmissaoNfse = DanfseFormatter::dateTime($inf->dataProcessamento);
        $view->numeroDps = DanfseFormatter::dash($infDps?->numeroDps);
        $view->serieDps = DanfseFormatter::dash($infDps?->serie);
        $view->dataHoraEmissaoDps = DanfseFormatter::dateTime($infDps?->dataEmissao);
        $view->emitenteNfse = $infDps?->tipoEmitente instanceof EmitenteDPS
            ? $infDps->tipoEmitente->label()
            : '-';
        $view->situacaoNfse = DanfseFormatter::truncate($inf->codigoStatus?->label() ?? '-', 40);
        $view->finalidade = '-';

        $this->mapPrestador($view, $infDps, $inf->emitente, $inf->localEmissao);
        $this->mapTomador($view, $infDps?->tomador, $inf->localPrestacao);
        $this->mapIntermediario($view, $infDps?->intermediario);
        $this->mapServico($view, $inf, $infDps);
        $this->mapIssqn($view, $inf, $infDps);
        $this->mapTotais($view, $inf, $infDps);
        $this->mapIbsCbs($view, $inf, $infDps);
        $this->mapInformacoesComplementares($view, $inf, $infDps);

        return $view;
    }

    private function detectWatermark(?CodigoStatus $status): ?DanfseWatermark
    {
        return match ($status) {
            CodigoStatus::NfseSubstituicaoGerada => DanfseWatermark::Substituída,
            default => null,
        };
    }

    private function mapPrestador(DanfseViewModel $view, ?InfDpsData $infDps, $emitente, ?string $localEmissao = null): void
    {
        $prestador = $infDps?->prestador;

        if ($prestador !== null) {
            $this->fillParticipante(
                $prestador->cpf,
                $prestador->cnpj,
                $prestador->nif,
                $prestador->inscricaoMunicipal,
                $prestador->telefone,
                $prestador->nome,
                $prestador->endereco,
                $prestador->email,
                $view->prestadorDocumento,
                $view->prestadorInscricaoMunicipal,
                $view->prestadorTelefone,
                $view->prestadorNome,
                $view->prestadorMunicipioUf,
                $view->prestadorCodigoIbgeCep,
            $view->prestadorEndereco,
            $view->prestadorEmail,
            $localEmissao,
        );

        if ($view->prestadorMunicipioUf !== '-') {
            $view->prestadorMunicipio = $view->prestadorMunicipioUf;
            $view->prestadorCep = $view->prestadorCodigoIbgeCep;
        }

            $regime = $prestador->regimeTributario;
            if ($regime) {
                $view->prestadorSimplesNacional = $regime->opcaoSimplesNacional instanceof OpcaoSimplesNacional
                    ? DanfseFormatter::truncate($regime->opcaoSimplesNacional->label(), 40)
                    : '-';
                $view->prestadorRegimeApuracaoSn = $regime->regimeApuracaoTributosSn instanceof RegimeApuracaoSN
                    ? DanfseFormatter::truncate($regime->regimeApuracaoTributosSn->label(), 80)
                    : '-';
            }
        }

        if ($emitente !== null) {
            if ($view->prestadorNome === '-' || $view->prestadorNome === '') {
                $view->prestadorNome = DanfseFormatter::truncate($emitente->nome, 80);
            }

            if ($view->prestadorDocumento === '-') {
                $view->prestadorDocumento = DanfseFormatter::documento($emitente->cpf, $emitente->cnpj);
            }

            if ($view->prestadorInscricaoMunicipal === '-') {
                $view->prestadorInscricaoMunicipal = DanfseFormatter::dash($emitente->inscricaoMunicipal);
            }

            if ($view->prestadorTelefone === '-') {
                $view->prestadorTelefone = DanfseFormatter::dash($emitente->telefone);
            }

            if ($view->prestadorEmail === '-') {
                $view->prestadorEmail = DanfseFormatter::dash($emitente->email);
            }

            if ($view->prestadorEndereco === '-') {
                $view->prestadorEndereco = DanfseFormatter::enderecoEmitente($emitente->endereco);
            }

            if ($emitente->endereco && ($view->prestadorMunicipioUf === '-' || $view->prestadorCodigoIbgeCep === '-')) {
                $cidade = DanfseFormatter::dash($localEmissao);
                $uf = DanfseFormatter::dash($emitente->endereco->uf);
                $view->prestadorMunicipio = $cidade !== '-' ? $cidade.' - '.$uf : '-';
                $view->prestadorMunicipioUf = $view->prestadorMunicipio;
                $view->prestadorCep = DanfseFormatter::formatCep($emitente->endereco->cep);
                $view->prestadorCodigoIbgeCep = $view->prestadorCep;
            }
        }
    }

    private function mapTomador(DanfseViewModel $view, ?TomadorData $tomador, ?string $localPrestacao = null): void
    {
        if ($tomador === null || (! $tomador->cpf && ! $tomador->cnpj && ! $tomador->nif && ! $tomador->nome)) {
            $view->tomadorIdentificado = false;

            return;
        }

        $view->tomadorIdentificado = true;
        $this->fillParticipante(
            $tomador->cpf,
            $tomador->cnpj,
            $tomador->nif,
            $tomador->inscricaoMunicipal,
            $tomador->telefone,
            $tomador->nome,
            $tomador->endereco,
            $tomador->email,
            $view->tomadorDocumento,
            $view->tomadorInscricaoMunicipal,
            $view->tomadorTelefone,
            $view->tomadorNome,
            $view->tomadorMunicipioUf,
            $view->tomadorCodigoIbgeCep,
            $view->tomadorEndereco,
            $view->tomadorEmail,
            $localPrestacao,
        );

        if ($view->tomadorMunicipioUf !== '-') {
            $view->tomadorMunicipio = $view->tomadorMunicipioUf;
            $view->tomadorCep = $view->tomadorCodigoIbgeCep;
        }
    }

    private function mapIntermediario(DanfseViewModel $view, $intermediario): void
    {
        if ($intermediario === null || (! $intermediario->cpf && ! $intermediario->cnpj && ! $intermediario->nif && ! $intermediario->nome)) {
            $view->intermediarioIdentificado = false;

            return;
        }

        $view->intermediarioIdentificado = true;
        $this->fillParticipante(
            $intermediario->cpf,
            $intermediario->cnpj,
            $intermediario->nif,
            $intermediario->inscricaoMunicipal,
            $intermediario->telefone,
            $intermediario->nome,
            $intermediario->endereco,
            $intermediario->email,
            $view->intermediarioDocumento,
            $view->intermediarioInscricaoMunicipal,
            $view->intermediarioTelefone,
            $view->intermediarioNome,
            $view->intermediarioMunicipioUf,
            $view->intermediarioCodigoIbgeCep,
            $view->intermediarioEndereco,
            $view->intermediarioEmail,
        );
    }

    private function fillParticipante(
        ?string $cpf,
        ?string $cnpj,
        ?string $nif,
        ?string $im,
        ?string $telefone,
        ?string $nome,
        $endereco,
        ?string $email,
        string &$documento,
        string &$inscricao,
        string &$fone,
        string &$nomeOut,
        string &$municipioUf,
        string &$codigoCep,
        string &$enderecoOut,
        string &$emailOut,
        ?string $nomeMunicipioFallback = null,
    ): void {
        $documento = DanfseFormatter::documento($cpf, $cnpj, $nif);
        $inscricao = DanfseFormatter::dash($im);
        $fone = DanfseFormatter::formatTelefone($telefone);
        $nomeOut = DanfseFormatter::truncate($nome, 120);
        $emailOut = DanfseFormatter::dash($email);
        $enderecoOut = DanfseFormatter::enderecoNacional($endereco);

        if ($endereco) {
            $uf = property_exists($endereco, 'uf') ? ($endereco->uf ?? null) : null;
            $cidade = DanfseFormatter::dash($nomeMunicipioFallback);
            if ($cidade !== '-') {
                $municipioDisplay = $uf ? $cidade.' - '.$uf : $cidade;
            } else {
                $municipioDisplay = DanfseFormatter::dash($endereco->codigoMunicipio).($uf ? ' - '.$uf : '');
            }
            $municipioUf = $municipioDisplay ?: '-';
            $codigoCep = DanfseFormatter::formatCep($endereco->cep);
        }
    }

    private function mapServico(DanfseViewModel $view, $inf, ?InfDpsData $infDps): void
    {
        $servico = $infDps?->servico;
        $codigoServico = $servico?->codigoServico;

        $codTribNac = $codigoServico?->codigoTributacaoNacional;
        $codTribMun = $codigoServico?->codigoTributacaoMunicipal;
        $view->codigoTributacaoNacional = DanfseFormatter::codigoTributacaoNacional(
            $codTribNac,
            $inf->descricaoTributacaoNacional
        );
        $view->codigoTributacaoMunicipal = DanfseFormatter::dash($codTribMun);
        $view->codigoTributacao = $view->codigoTributacaoNacional;

        $view->codigoNbs = DanfseFormatter::dash($codigoServico?->codigoNbs);
        $view->localPrestacao = DanfseFormatter::dash($inf->localPrestacao ?? $inf->localEmissao);
        $view->paisPrestacao = '-';

        $descMun = $inf->descricaoTributacaoMunicipal;
        $descNac = $inf->descricaoTributacaoNacional;
        $view->descricaoTributacao = DanfseFormatter::truncate($descMun ?: $descNac, 170);
        $view->descricaoServico = DanfseFormatter::truncate($codigoServico?->descricaoServico, 1300);
    }

    private function mapIssqn(DanfseViewModel $view, $inf, ?InfDpsData $infDps): void
    {
        $tributacao = $infDps?->valores?->tributacao;
        $tribIssqn = $tributacao?->tributacaoIssqn;

        if ($tribIssqn === TributacaoIssqn::NaoIncidencia || $tribIssqn === TributacaoIssqn::ExportacaoServico) {
            $view->exibirTributacaoIssqn = false;

            return;
        }

        $view->tipoTributacaoIssqn = $tribIssqn instanceof TributacaoIssqn
            ? $tribIssqn->label()
            : '-';
        $view->municipioIncidenciaIssqn = DanfseFormatter::dash($inf->nomeLocalIncidencia);
        $view->regimeEspecialIssqn = $infDps?->prestador?->regimeTributario?->regimeEspecialTributacao instanceof RegimeEspecialTributacao
            ? DanfseFormatter::truncate($infDps->prestador->regimeTributario->regimeEspecialTributacao->label(), 27)
            : '-';
        $view->retencaoIssqn = $tributacao?->tipoRetencaoIssqn instanceof TipoRetencaoIssqn
            ? $tributacao->tipoRetencaoIssqn->label()
            : '-';
        $view->issqnRetido = $view->retencaoIssqn;

        $view->baseCalculoIssqn = DanfseFormatter::moneyReal($inf->valores?->baseCalculo);
        $view->aliquotaIssqn = DanfseFormatter::percent($inf->valores?->aliquotaAplicada);
        $view->valorIssqn = DanfseFormatter::moneyReal($inf->valores?->valorIssqn);
        $view->valorServico = DanfseFormatter::moneyReal($infDps?->valores?->valorServicoPrestado?->valorServico);

        $this->mapRetencoesFederais($view, $tributacao);
    }

    private function mapRetencoesFederais(DanfseViewModel $view, ?TributacaoData $tributacao): void
    {
        if ($tributacao === null) {
            return;
        }

        $view->irrf = DanfseFormatter::moneyReal($tributacao->valorRetidoIrrf);
        $view->contribuicoesSociaisRetidas = DanfseFormatter::moneyReal($tributacao->valorRetidoCsll);
        $view->pisProprio = DanfseFormatter::moneyReal($tributacao->valorPis);
        $view->cofinsProprio = DanfseFormatter::moneyReal($tributacao->valorCofins);

        $pis = $tributacao->valorPis ?? 0;
        $cofins = $tributacao->valorCofins ?? 0;
        if ($pis > 0 || $cofins > 0) {
            $view->pisCofinsDebitoProprio = DanfseFormatter::moneyReal($pis + $cofins);
        }
    }

    private function mapTotais(DanfseViewModel $view, $inf, ?InfDpsData $infDps): void
    {
        $valoresDps = $infDps?->valores;

        $view->valorOperacao = DanfseFormatter::moneyReal($valoresDps?->valorServicoPrestado?->valorServico);
        $view->valorServico = $view->valorOperacao;
        $view->descontoIncondicionado = DanfseFormatter::moneyReal($valoresDps?->desconto?->valorDescontoIncondicionado);
        $view->descontoCondicionado = DanfseFormatter::moneyReal($valoresDps?->desconto?->valorDescontoCondicionado);
        $view->totalRetencoes = DanfseFormatter::moneyReal($inf->valores?->valorTotalRetido);
        $view->valorLiquidoNfse = DanfseFormatter::moneyReal($inf->valores?->valorLiquido);
    }

    private function mapIbsCbs(DanfseViewModel $view, $inf, ?InfDpsData $infDps): void
    {
        $info = $infDps?->ibscbs;
        $computed = $inf->ibscbs;

        if ($info?->finalidade) {
            $view->finalidade = DanfseFormatter::truncate($info->finalidade->label(), 40);
        }

        $situacao = $info?->valores?->tributacao?->situacaoClassificacao;
        if ($situacao) {
            $view->cstClassTrib = DanfseFormatter::dash($situacao->codigoSituacaoTributaria)
                .' / '.DanfseFormatter::dash($situacao->codigoClassificacaoTributaria);
            if ($view->cstClassTrib === '- / -') {
                $view->cstClassTrib = '-';
            }
        }

        if ($info?->codigoIndicadorOperacao) {
            $view->indicadorOperacaoIbs = DanfseFormatter::dash($info->codigoIndicadorOperacao);
        }

        if ($computed === null) {
            return;
        }

        $view->exibirTributacaoIbsCbs = true;
        $valores = $computed->valores;
        $totais = $computed->totalizadores;

        $view->baseCalculoIbsCbs = DanfseFormatter::moneyReal($valores?->baseCalculo);
        $view->reducaoAliquotaIbsCbs = DanfseFormatter::percent($computed->percentualRedutor);

        $view->aliquotaIbs = DanfseFormatter::percent(
            ($valores?->uf?->aliquotaIbsUf ?? 0) + ($valores?->municipio?->aliquotaIbsMunicipal ?? 0)
        );
        $view->aliquotaEfetivaIbsEstadual = DanfseFormatter::percent($valores?->uf?->aliquotaEfetivaUf);
        $view->aliquotaEfetivaIbsMunicipal = DanfseFormatter::percent($valores?->municipio?->aliquotaEfetivaMunicipal);
        $view->valorIbsEstadual = DanfseFormatter::moneyReal($totais?->totalIbs?->totalUf?->valorIbsUf);
        $view->valorIbsMunicipal = DanfseFormatter::moneyReal($totais?->totalIbs?->totalMunicipal?->valorIbsMunicipal);
        $view->valorTotalIbs = DanfseFormatter::moneyReal($totais?->totalIbs?->valorTotalIbs);

        $view->aliquotaCbs = DanfseFormatter::percent($valores?->federal?->aliquotaCbs);
        $view->aliquotaEfetivaCbs = DanfseFormatter::percent($valores?->federal?->aliquotaEfetivaCbs);
        $view->valorTotalCbs = DanfseFormatter::moneyReal($totais?->totalCbs?->valorCbs);

        $totalIbs = $totais?->totalIbs?->valorTotalIbs ?? 0;
        $totalCbs = $totais?->totalCbs?->valorCbs ?? 0;
        $view->totalIbsCbs = DanfseFormatter::moneyReal($totalIbs + $totalCbs);
        $view->valorLiquidoComIbsCbs = DanfseFormatter::moneyReal($totais?->valorTotalNf);
    }

    private function mapInformacoesComplementares(DanfseViewModel $view, $inf, ?InfDpsData $infDps): void
    {
        $partes = [];

        $infoCompl = $infDps?->servico?->informacaoComplemento?->informacoesComplementares;
        if ($infoCompl) {
            $partes[] = 'Inf. Cont.: '.$infoCompl;
        }

        if ($inf->outrasInformacoes) {
            $partes[] = $inf->outrasInformacoes;
        }

        $tributacao = $infDps?->valores?->tributacao;
        if ($tributacao) {
            $view->totalTributosFederais = DanfseFormatter::moneyReal($tributacao->valorTotalTributosFederais);
            $view->totalTributosEstaduais = DanfseFormatter::moneyReal($tributacao->valorTotalTributosEstaduais);
            $view->totalTributosMunicipais = DanfseFormatter::moneyReal($tributacao->valorTotalTributosMunicipais);

            $totais = [];
            if ($tributacao->valorTotalTributosFederais !== null) {
                $totais[] = 'Federais: '.DanfseFormatter::moneyReal($tributacao->valorTotalTributosFederais);
            }
            if ($tributacao->valorTotalTributosEstaduais !== null) {
                $totais[] = 'Estaduais: '.DanfseFormatter::moneyReal($tributacao->valorTotalTributosEstaduais);
            }
            if ($tributacao->valorTotalTributosMunicipais !== null) {
                $totais[] = 'Municipais: '.DanfseFormatter::moneyReal($tributacao->valorTotalTributosMunicipais);
            }

            if ($totais !== []) {
                $partes[] = 'Totais Aproximados dos Tributos cfe. Lei nº 12.741/2012: '.implode('; ', $totais);
            }
        }

        $view->informacoesComplementares = DanfseFormatter::truncate(implode(' | ', $partes), 2000);
    }
}
