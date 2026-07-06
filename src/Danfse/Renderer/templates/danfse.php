<?php
/** @var \Nfse\Danfse\DanfseViewModel $viewModel */
$vm = $viewModel;
$e = static fn (?string $value): string => htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
$field = static function (string $label, string $value, int $cols = 1) use ($e): string {
    $class = match ($cols) {
        2 => 'col-2',
        3 => 'col-3',
        4 => 'col-4',
        default => 'col-1',
    };

    return <<<HTML
<div class="field {$class}">
    <div class="field-label">{$e($label)}</div>
    <div class="field-value">{$e($value)}</div>
</div>
HTML;
};
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
        @page { size: A4; margin: 8mm 10mm; }
        * { box-sizing: border-box; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 7.5pt;
            line-height: 1.25;
            color: #111;
            margin: 0;
            padding: 0;
        }
        .page {
            position: relative;
            width: 190mm;
            margin: 0 auto;
        }
        .watermark-cancelada::before,
        .watermark-substituida::before {
            content: attr(data-watermark);
            position: absolute;
            top: 45%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-35deg);
            font-size: 44pt;
            font-weight: bold;
            color: rgba(180, 0, 0, 0.16);
            white-space: nowrap;
            z-index: 0;
            pointer-events: none;
        }
        .banner-homologacao {
            background: #000;
            color: #fff;
            text-align: center;
            font-weight: bold;
            padding: 5px 8px;
            margin-bottom: 8px;
            font-size: 8pt;
        }
        .top-header {
            display: grid;
            grid-template-columns: 95px 1fr 170px;
            gap: 10px;
            align-items: start;
            padding-bottom: 8px;
            border-bottom: 1px solid #222;
        }
        .logo-nfse {
            width: 88px;
        }
        .doc-title {
            text-align: center;
            padding-top: 4px;
        }
        .doc-title h1 {
            margin: 0;
            font-size: 14pt;
            font-weight: bold;
        }
        .doc-title h2 {
            margin: 2px 0 0;
            font-size: 9pt;
            font-weight: bold;
        }
        .prefeitura {
            text-align: right;
            font-size: 7pt;
            line-height: 1.35;
        }
        .prefeitura strong { display: block; font-size: 7.5pt; }
        .chave-row {
            display: grid;
            grid-template-columns: 1fr 118px;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #222;
        }
        .chave-label {
            font-weight: bold;
            margin-bottom: 2px;
        }
        .chave-value {
            font-size: 8pt;
            letter-spacing: 0.3px;
            word-break: break-all;
        }
        .qr-box { text-align: center; }
        .qr-box img { width: 96px; height: 96px; display: block; margin: 0 auto 4px; }
        .qr-note { font-size: 6.5pt; line-height: 1.3; color: #333; }
        .section { padding: 7px 0 5px; border-bottom: 1px solid #222; }
        .section-title {
            font-weight: bold;
            font-size: 8pt;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .section-subtitle {
            font-weight: normal;
            text-transform: none;
            font-size: 7.5pt;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 6px 10px;
        }
        .grid-3 { grid-template-columns: repeat(3, 1fr); }
        .col-1 { grid-column: span 1; }
        .col-2 { grid-column: span 2; }
        .col-3 { grid-column: span 3; }
        .col-4 { grid-column: span 4; }
        .field-label {
            font-weight: bold;
            font-size: 7pt;
            margin-bottom: 1px;
        }
        .field-value {
            font-size: 7.5pt;
            min-height: 11px;
            word-break: break-word;
        }
        .center-note {
            text-align: center;
            font-weight: bold;
            font-size: 7.5pt;
            padding: 4px 0;
        }
        .descricao-servico .field-value {
            min-height: 18px;
        }
        .valor-destaque .field-value {
            font-weight: bold;
        }
    </style>
</head>
<body>
<div class="page <?= $e($watermarkClass) ?>"<?= $vm->marcaDagua ? ' data-watermark="'.$e($vm->marcaDagua->value).'"' : '' ?>>
    <?php if ($vm->exibirAvisoHomologacao): ?>
        <div class="banner-homologacao">NFS-e SEM VALIDADE JURÍDICA — AMBIENTE DE HOMOLOGAÇÃO</div>
    <?php endif; ?>

    <div class="top-header">
        <div>
            <svg class="logo-nfse" viewBox="0 0 120 48" xmlns="http://www.w3.org/2000/svg" aria-label="NFSe">
                <text x="0" y="24" font-family="Arial Black, Arial" font-size="22" font-weight="900" fill="#2E7D32">NF</text>
                <text x="34" y="24" font-family="Arial Black, Arial" font-size="22" font-weight="900" fill="#1565C0">Se</text>
                <text x="0" y="40" font-family="Arial" font-size="7" fill="#333">Nota Fiscal de Serviço eletrônica</text>
            </svg>
        </div>
        <div class="doc-title">
            <h1><?= $e($vm->tituloDocumento) ?></h1>
            <h2><?= $e($vm->subtituloDocumento) ?></h2>
        </div>
        <div class="prefeitura">
            <strong><?= $e($vm->prefeituraNome) ?></strong>
            <?= $e($vm->prefeituraSubtitulo) !== '-' ? $e($vm->prefeituraSubtitulo).'<br>' : '' ?>
            <?= $e($vm->prefeituraTelefone) !== '-' ? $e($vm->prefeituraTelefone).'<br>' : '' ?>
            <?= $e($vm->prefeituraEmail) !== '-' ? $e($vm->prefeituraEmail) : '' ?>
        </div>
    </div>

    <div class="chave-row">
        <div>
            <div class="chave-label">Chave de Acesso da NFS-e</div>
            <div class="chave-value"><?= $e($vm->chaveAcesso) ?></div>
        </div>
        <div class="qr-box">
            <img src="<?= $e($vm->qrCodeDataUri) ?>" alt="QR Code">
            <div class="qr-note">A autenticidade desta NFS-e pode ser verificada pela leitura deste código QR ou pela consulta da chave de acesso no portal nacional da NFS-e</div>
        </div>
    </div>

    <div class="section">
        <div class="grid grid-3">
            <?= $field('Número da NFS-e', $vm->numeroNfse) ?>
            <?= $field('Competência da NFS-e', $vm->competencia) ?>
            <?= $field('Data e Hora da emissão da NFS-e', $vm->dataHoraEmissaoNfse) ?>
            <?= $field('Número da DPS', $vm->numeroDps) ?>
            <?= $field('Série da DPS', $vm->serieDps) ?>
            <?= $field('Data e Hora da emissão da DPS', $vm->dataHoraEmissaoDps) ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">EMITENTE DA NFS-e <span class="section-subtitle">Prestador do Serviço</span></div>
        <div class="grid">
            <?= $field('CNPJ / CPF / NIF', $vm->prestadorDocumento) ?>
            <?= $field('Inscrição Municipal', $vm->prestadorInscricaoMunicipal) ?>
            <?= $field('Telefone', $vm->prestadorTelefone) ?>
            <div class="col-1"></div>
            <?= $field('Nome / Nome Empresarial', $vm->prestadorNome, 2) ?>
            <?= $field('E-mail', $vm->prestadorEmail, 2) ?>
            <?= $field('Endereço', $vm->prestadorEndereco, 2) ?>
            <?= $field('Município', $vm->prestadorMunicipio) ?>
            <?= $field('CEP', $vm->prestadorCep) ?>
            <?= $field('Simples Nacional na Data de Competência', $vm->prestadorSimplesNacional, 2) ?>
            <?= $field('Regime de Apuração Tributária pelo SN', $vm->prestadorRegimeApuracaoSn, 2) ?>
        </div>
    </div>

    <?php if ($vm->tomadorIdentificado): ?>
    <div class="section">
        <div class="section-title">TOMADOR DO SERVIÇO</div>
        <div class="grid">
            <?= $field('CNPJ / CPF / NIF', $vm->tomadorDocumento) ?>
            <?= $field('Inscrição Municipal', $vm->tomadorInscricaoMunicipal) ?>
            <?= $field('Telefone', $vm->tomadorTelefone) ?>
            <div class="col-1"></div>
            <?= $field('Nome / Nome Empresarial', $vm->tomadorNome, 2) ?>
            <?= $field('E-mail', $vm->tomadorEmail, 2) ?>
            <?= $field('Endereço', $vm->tomadorEndereco, 2) ?>
            <?= $field('Município', $vm->tomadorMunicipio) ?>
            <?= $field('CEP', $vm->tomadorCep) ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="section">
        <?php if ($vm->intermediarioIdentificado): ?>
            <div class="section-title">INTERMEDIÁRIO DO SERVIÇO</div>
            <div class="grid">
                <?= $field('CNPJ / CPF / NIF', $vm->intermediarioDocumento) ?>
                <?= $field('Inscrição Municipal', $vm->intermediarioInscricaoMunicipal) ?>
                <?= $field('Telefone', $vm->intermediarioTelefone) ?>
                <?= $field('Nome / Nome Empresarial', $vm->intermediarioNome) ?>
            </div>
        <?php else: ?>
            <div class="center-note">INTERMEDIÁRIO DO SERVIÇO NÃO IDENTIFICADO NA NFS-e</div>
        <?php endif; ?>
    </div>

    <div class="section">
        <div class="section-title">SERVIÇO PRESTADO</div>
        <div class="grid">
            <?= $field('Código de Tributação Nacional', $vm->codigoTributacaoNacional, 2) ?>
            <?= $field('Código de Tributação Municipal', $vm->codigoTributacaoMunicipal) ?>
            <?= $field('Local da Prestação', $vm->localPrestacao) ?>
            <?= $field('País da Prestação', $vm->paisPrestacao) ?>
        </div>
        <div class="grid descricao-servico" style="margin-top:6px;">
            <?= $field('Descrição do Serviço', $vm->descricaoServico, 4) ?>
        </div>
    </div>

    <?php if ($vm->exibirTributacaoIssqn): ?>
    <div class="section">
        <div class="section-title">TRIBUTAÇÃO MUNICIPAL</div>
        <div class="grid">
            <?= $field('Tributação do ISSQN', $vm->tipoTributacaoIssqn) ?>
            <?= $field('País Resultado da Prestação do Serviço', '-') ?>
            <?= $field('Município de Incidência do ISSQN', $vm->municipioIncidenciaIssqn) ?>
            <?= $field('Regime Especial de Tributação', $vm->regimeEspecialIssqn) ?>
            <?= $field('Tipo de Imunidade', $vm->tipoImunidadeIssqn) ?>
            <?= $field('Suspensão da Exigibilidade do ISSQN', $vm->suspensaoIssqn) ?>
            <?= $field('Número Processo Suspensão', $vm->numeroProcessoSuspensao) ?>
            <?= $field('Benefício Municipal', $vm->beneficioMunicipal) ?>
            <?= $field('Valor do Serviço', $vm->valorServico) ?>
            <?= $field('Desconto Incondicionado', $vm->descontoIncondicionadoIssqn !== '-' ? $vm->descontoIncondicionadoIssqn : $vm->descontoIncondicionado) ?>
            <?= $field('Total Deduções/Reduções', $vm->totalDeducoes) ?>
            <?= $field('Cálculo do BM', $vm->calculoBm) ?>
            <?= $field('BC ISSQN', $vm->baseCalculoIssqn) ?>
            <?= $field('Alíquota Aplicada', $vm->aliquotaIssqn) ?>
            <?= $field('Retenção do ISSQN', $vm->retencaoIssqn) ?>
            <?= $field('ISSQN Apurado', $vm->valorIssqn) ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-title">TRIBUTAÇÃO FEDERAL</div>
        <div class="grid">
            <?= $field('IRRF', $vm->irrf) ?>
            <?= $field('Contribuição Previdenciária - Retida', $vm->contribuicaoPrevidenciaria) ?>
            <?= $field('Contribuições Sociais - Retidas', $vm->contribuicoesSociaisRetidas) ?>
            <?= $field('Descrição Contrib. Sociais - Retidas', $vm->descricaoContribuicoesRetidas) ?>
            <?= $field('PIS - Débito Apuração Própria', $vm->pisProprio) ?>
            <?= $field('COFINS - Débito Apuração Própria', $vm->cofinsProprio) ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">VALOR TOTAL DA NFS-E</div>
        <div class="grid">
            <?= $field('Valor do Serviço', $vm->valorServico) ?>
            <?= $field('Desconto Condicionado', $vm->descontoCondicionado) ?>
            <?= $field('Desconto Incondicionado', $vm->descontoIncondicionado) ?>
            <?= $field('ISSQN Retido', $vm->issqnRetido) ?>
            <?= $field('Total das Retenções Federais', $vm->totalRetencoes) ?>
            <?= $field('PIS/COFINS - Débito Apur. Própria', $vm->pisCofinsDebitoProprio) ?>
            <div class="col-1"></div>
            <div class="field col-1 valor-destaque">
                <div class="field-label">Valor Líquido da NFS-e</div>
                <div class="field-value"><?= $e($vm->valorLiquidoNfse) ?></div>
            </div>
        </div>
    </div>

    <?php if ($vm->exibirTributacaoIbsCbs): ?>
    <div class="section">
        <div class="section-title">TRIBUTAÇÃO IBS/CBS</div>
        <div class="grid">
            <?= $field('Finalidade', $vm->finalidade) ?>
            <?= $field('CST / cClassTrib', $vm->cstClassTrib) ?>
            <?= $field('Cód. Indicador Operação', $vm->indicadorOperacaoIbs) ?>
            <?= $field('Base de Cálculo IBS/CBS', $vm->baseCalculoIbsCbs) ?>
            <?= $field('Total IBS', $vm->valorTotalIbs) ?>
            <?= $field('Total CBS', $vm->valorTotalCbs) ?>
            <?= $field('Total IBS + CBS', $vm->totalIbsCbs) ?>
            <?= $field('Valor Total NF c/ IBS/CBS', $vm->valorLiquidoComIbsCbs) ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="section">
        <div class="section-title">TOTAIS APROXIMADOS DOS TRIBUTOS</div>
        <div class="grid grid-3">
            <?= $field('Federais', $vm->totalTributosFederais) ?>
            <?= $field('Estaduais', $vm->totalTributosEstaduais) ?>
            <?= $field('Municipais', $vm->totalTributosMunicipais) ?>
        </div>
    </div>

    <div class="section">
        <div class="section-title">INFORMAÇÕES COMPLEMENTARES</div>
        <div class="field-value" style="min-height:24px;"><?= $e($vm->informacoesComplementares !== '-' ? $vm->informacoesComplementares : '') ?></div>
    </div>
</div>
</body>
</html>
