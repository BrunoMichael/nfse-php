<?php
/** @var \Nfse\Danfse\DanfseViewModel $viewModel */
$vm = $viewModel;
$e = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$watermarkClass = match ($vm->marcaDagua) {
    \Nfse\Danfse\DanfseWatermark::Cancelada => 'watermark-cancelada',
    \Nfse\Danfse\DanfseWatermark::Substituída => 'watermark-substituida',
    default => '',
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title><?= $e($vm->tituloDocumento) ?></title>
    <style>
        @page { size: A4; margin: 10mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 8pt;
            color: #000;
            margin: 0;
            padding: 0;
        }
        .page {
            position: relative;
            width: 190mm;
            min-height: 277mm;
            margin: 0 auto;
        }
        .watermark-cancelada::before,
        .watermark-substituida::before {
            content: attr(data-watermark);
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 48pt;
            font-weight: bold;
            color: rgba(200, 0, 0, 0.18);
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }
        .banner-homologacao {
            background: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 4px;
            margin-bottom: 6px;
        }
        .header {
            display: flex;
            gap: 8px;
            border: 1px solid #000;
            padding: 6px;
            margin-bottom: 6px;
        }
        .header-left { flex: 1; }
        .header-right { width: 90px; text-align: center; }
        .header-right img { width: 80px; height: 80px; }
        .titulo { font-size: 11pt; font-weight: bold; margin-bottom: 4px; }
        .chave { font-size: 7pt; word-break: break-all; }
        .section {
            border: 1px solid #000;
            margin-bottom: 6px;
        }
        .section-title {
            background: #e8e8e8;
            font-weight: bold;
            padding: 3px 6px;
            border-bottom: 1px solid #000;
        }
        .section-body { padding: 4px 6px; }
        table.grid { width: 100%; border-collapse: collapse; }
        table.grid td, table.grid th {
            border: 1px solid #ccc;
            padding: 2px 4px;
            vertical-align: top;
        }
        table.grid th {
            background: #f5f5f5;
            font-weight: bold;
            width: 22%;
            white-space: nowrap;
        }
        .row-2 td { width: 50%; }
        .text-block { line-height: 1.35; }
        .totais td:last-child { text-align: right; white-space: nowrap; }
        .footer-note {
            font-size: 6.5pt;
            margin-top: 8px;
            text-align: center;
            color: #444;
        }
    </style>
</head>
<body>
<div class="page <?= $e($watermarkClass) ?>"<?= $vm->marcaDagua ? ' data-watermark="'.$e($vm->marcaDagua->value).'"' : '' ?>>
    <?php if ($vm->exibirAvisoHomologacao): ?>
        <div class="banner-homologacao">NFS-e SEM VALIDADE JURÍDICA — AMBIENTE DE HOMOLOGAÇÃO</div>
    <?php endif; ?>

    <div class="header">
        <div class="header-left">
            <div class="titulo"><?= $e($vm->tituloDocumento) ?></div>
            <div><strong>Município emissor:</strong> <?= $e($vm->municipioEmissor) ?></div>
            <div><strong>Ambiente gerador:</strong> <?= $e($vm->ambienteGerador) ?> | <strong>Tipo ambiente:</strong> <?= $e($vm->tipoAmbiente) ?></div>
            <div class="chave"><strong>Chave de acesso:</strong> <?= $e($vm->chaveAcesso) ?></div>
            <div class="chave"><strong>Consulta:</strong> <?= $e($vm->urlConsulta) ?></div>
        </div>
        <div class="header-right">
            <img src="<?= $e($vm->qrCodeDataUri) ?>" alt="QR Code NFS-e">
        </div>
    </div>

    <div class="section">
        <div class="section-title">Identificação da NFS-e</div>
        <div class="section-body">
            <table class="grid">
                <tr>
                    <th>Número NFS-e</th><td><?= $e($vm->numeroNfse) ?></td>
                    <th>Competência</th><td><?= $e($vm->competencia) ?></td>
                </tr>
                <tr>
                    <th>Data/hora emissão NFS-e</th><td><?= $e($vm->dataHoraEmissaoNfse) ?></td>
                    <th>Situação</th><td><?= $e($vm->situacaoNfse) ?></td>
                </tr>
                <tr>
                    <th>Número DPS</th><td><?= $e($vm->numeroDps) ?></td>
                    <th>Série DPS</th><td><?= $e($vm->serieDps) ?></td>
                </tr>
                <tr>
                    <th>Data/hora emissão DPS</th><td><?= $e($vm->dataHoraEmissaoDps) ?></td>
                    <th>Emitente NFS-e</th><td><?= $e($vm->emitenteNfse) ?></td>
                </tr>
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Prestador do Serviço</div>
        <div class="section-body">
            <table class="grid">
                <tr><th>CPF/CNPJ</th><td colspan="3"><?= $e($vm->prestadorDocumento) ?></td></tr>
                <tr><th>Inscrição Municipal</th><td><?= $e($vm->prestadorInscricaoMunicipal) ?></td><th>Telefone</th><td><?= $e($vm->prestadorTelefone) ?></td></tr>
                <tr><th>Nome/Razão Social</th><td colspan="3"><?= $e($vm->prestadorNome) ?></td></tr>
                <tr><th>Endereço</th><td colspan="3"><?= $e($vm->prestadorEndereco) ?></td></tr>
                <tr><th>Município/UF</th><td><?= $e($vm->prestadorMunicipioUf) ?></td><th>Cód. IBGE / CEP</th><td><?= $e($vm->prestadorCodigoIbgeCep) ?></td></tr>
                <tr><th>E-mail</th><td colspan="3"><?= $e($vm->prestadorEmail) ?></td></tr>
                <tr><th>Simples Nacional</th><td><?= $e($vm->prestadorSimplesNacional) ?></td><th>Regime apuração SN</th><td><?= $e($vm->prestadorRegimeApuracaoSn) ?></td></tr>
            </table>
        </div>
    </div>

    <?php if ($vm->tomadorIdentificado): ?>
    <div class="section">
        <div class="section-title">Tomador do Serviço</div>
        <div class="section-body">
            <table class="grid">
                <tr><th>CPF/CNPJ</th><td colspan="3"><?= $e($vm->tomadorDocumento) ?></td></tr>
                <tr><th>Inscrição Municipal</th><td><?= $e($vm->tomadorInscricaoMunicipal) ?></td><th>Telefone</th><td><?= $e($vm->tomadorTelefone) ?></td></tr>
                <tr><th>Nome/Razão Social</th><td colspan="3"><?= $e($vm->tomadorNome) ?></td></tr>
                <tr><th>Endereço</th><td colspan="3"><?= $e($vm->tomadorEndereco) ?></td></tr>
                <tr><th>Município/UF</th><td><?= $e($vm->tomadorMunicipioUf) ?></td><th>Cód. IBGE / CEP</th><td><?= $e($vm->tomadorCodigoIbgeCep) ?></td></tr>
                <tr><th>E-mail</th><td colspan="3"><?= $e($vm->tomadorEmail) ?></td></tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($vm->intermediarioIdentificado): ?>
    <div class="section">
        <div class="section-title">Intermediário do Serviço</div>
        <div class="section-body">
            <table class="grid">
                <tr><th>CPF/CNPJ</th><td colspan="3"><?= $e($vm->intermediarioDocumento) ?></td></tr>
                <tr><th>Nome/Razão Social</th><td colspan="3"><?= $e($vm->intermediarioNome) ?></td></tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-title">Serviço Prestado</div>
        <div class="section-body">
            <table class="grid">
                <tr><th>Cód. tributação</th><td><?= $e($vm->codigoTributacao) ?></td><th>Cód. NBS</th><td><?= $e($vm->codigoNbs) ?></td></tr>
                <tr><th>Local prestação</th><td colspan="3"><?= $e($vm->localPrestacao) ?></td></tr>
                <tr><th>Descrição tributação</th><td colspan="3" class="text-block"><?= $e($vm->descricaoTributacao) ?></td></tr>
                <tr><th>Descrição serviço</th><td colspan="3" class="text-block"><?= $e($vm->descricaoServico) ?></td></tr>
            </table>
        </div>
    </div>

    <?php if ($vm->exibirTributacaoIssqn): ?>
    <div class="section">
        <div class="section-title">Tributação ISSQN</div>
        <div class="section-body">
            <table class="grid">
                <tr><th>Tipo tributação</th><td><?= $e($vm->tipoTributacaoIssqn) ?></td><th>Retenção ISSQN</th><td><?= $e($vm->retencaoIssqn) ?></td></tr>
                <tr><th>Município incidência</th><td colspan="3"><?= $e($vm->municipioIncidenciaIssqn) ?></td></tr>
                <tr><th>Regime especial</th><td colspan="3"><?= $e($vm->regimeEspecialIssqn) ?></td></tr>
                <tr>
                    <th>Base cálculo</th><td><?= $e($vm->baseCalculoIssqn) ?></td>
                    <th>Alíquota</th><td><?= $e($vm->aliquotaIssqn) ?></td>
                </tr>
                <tr><th>Valor ISSQN</th><td colspan="3"><?= $e($vm->valorIssqn) ?></td></tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($vm->exibirTributacaoIbsCbs): ?>
    <div class="section">
        <div class="section-title">Tributação IBS/CBS (Reforma Tributária)</div>
        <div class="section-body">
            <table class="grid">
                <tr><th>Finalidade</th><td><?= $e($vm->finalidade) ?></td><th>CST / cClassTrib</th><td><?= $e($vm->cstClassTrib) ?></td></tr>
                <tr><th>Cód. indicador operação</th><td colspan="3"><?= $e($vm->indicadorOperacaoIbs) ?></td></tr>
                <tr><th>Base cálculo IBS/CBS</th><td><?= $e($vm->baseCalculoIbsCbs) ?></td><th>Redutor compra gov.</th><td><?= $e($vm->reducaoAliquotaIbsCbs) ?></td></tr>
                <tr>
                    <th>Alíq. IBS UF / Mun.</th><td><?= $e($vm->aliquotaEfetivaIbsEstadual) ?> / <?= $e($vm->aliquotaEfetivaIbsMunicipal) ?></td>
                    <th>Valor IBS UF / Mun.</th><td><?= $e($vm->valorIbsEstadual) ?> / <?= $e($vm->valorIbsMunicipal) ?></td>
                </tr>
                <tr><th>Total IBS</th><td><?= $e($vm->valorTotalIbs) ?></td><th>Alíq. CBS efetiva</th><td><?= $e($vm->aliquotaEfetivaCbs) ?></td></tr>
                <tr><th>Total CBS</th><td><?= $e($vm->valorTotalCbs) ?></td><th>Total IBS + CBS</th><td><strong><?= $e($vm->totalIbsCbs) ?></strong></td></tr>
                <tr><th colspan="3">Valor total NF c/ IBS/CBS</th><td><strong><?= $e($vm->valorLiquidoComIbsCbs) ?></strong></td></tr>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-title">Retenções Federais e Totais</div>
        <div class="section-body">
            <table class="grid totais">
                <tr><th>IRRF</th><td><?= $e($vm->irrf) ?></td><th>PIS próprio</th><td><?= $e($vm->pisProprio) ?></td></tr>
                <tr><th>Contrib. sociais retidas</th><td><?= $e($vm->contribuicoesSociaisRetidas) ?></td><th>COFINS próprio</th><td><?= $e($vm->cofinsProprio) ?></td></tr>
                <tr><th>Valor operação</th><td><?= $e($vm->valorOperacao) ?></td><th>Desc. incondicionado</th><td><?= $e($vm->descontoIncondicionado) ?></td></tr>
                <tr><th>Desc. condicionado</th><td><?= $e($vm->descontoCondicionado) ?></td><th>Total retenções</th><td><?= $e($vm->totalRetencoes) ?></td></tr>
                <tr><th colspan="3">Valor líquido NFS-e</th><td><strong><?= $e($vm->valorLiquidoNfse) ?></strong></td></tr>
            </table>
        </div>
    </div>

    <?php if ($vm->informacoesComplementares !== '-' && $vm->informacoesComplementares !== ''): ?>
    <div class="section">
        <div class="section-title">Informações Complementares</div>
        <div class="section-body text-block"><?= $e($vm->informacoesComplementares) ?></div>
    </div>
    <?php endif; ?>

    <div class="footer-note">
        Documento auxiliar da NFS-e gerado localmente conforme NT-008 (DANFSe v2.0).
    </div>
</div>
</body>
</html>
