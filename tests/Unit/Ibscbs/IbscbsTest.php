<?php

use Nfse\Enums\FinalidadeNfse;
use Nfse\Enums\IndicadorConsumoFinal;
use Nfse\Enums\IndicadorDestinatario;
use Nfse\Xml\NfseXmlParser;

it('parses nfse-level ibscbs from pis zerado fixture', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPisZeradoCofinsSobreFaturamentoPreenchido.xml');
    $nfse = (new NfseXmlParser)->parse($xml);

    $ibscbs = $nfse->infNfse->ibscbs;

    expect($ibscbs)->not->toBeNull()
        ->and($ibscbs->codigoLocalidadeIncidencia)->toBe('3303302')
        ->and($ibscbs->nomeLocalidadeIncidencia)->toBe('Niterói')
        ->and($ibscbs->valores?->baseCalculo)->toBe(8740.00)
        ->and($ibscbs->valores?->uf?->aliquotaEfetivaUf)->toBe(0.10)
        ->and($ibscbs->valores?->federal?->aliquotaEfetivaCbs)->toBe(0.90)
        ->and($ibscbs->totalizadores?->totalIbs?->valorTotalIbs)->toBe(8.74)
        ->and($ibscbs->totalizadores?->totalCbs?->valorCbs)->toBe(78.66)
        ->and($ibscbs->totalizadores?->valorTotalNf)->toBe(9385.00);
});

it('parses dps-level info ibscbs from pis zerado fixture', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPisZeradoCofinsSobreFaturamentoPreenchido.xml');
    $nfse = (new NfseXmlParser)->parse($xml);

    $info = $nfse->infNfse->dps?->infDps?->ibscbs;

    expect($info)->not->toBeNull()
        ->and($info->finalidade)->toBe(FinalidadeNfse::Regular)
        ->and($info->indicadorConsumoFinal)->toBe(IndicadorConsumoFinal::Sim)
        ->and($info->codigoIndicadorOperacao)->toBe('100301')
        ->and($info->indicadorDestinatario)->toBe(IndicadorDestinatario::TomadorEhDestinatario)
        ->and($info->valores?->tributacao?->situacaoClassificacao?->codigoSituacaoTributaria)->toBe('000')
        ->and($info->valores?->tributacao?->situacaoClassificacao?->codigoClassificacaoTributaria)->toBe('000001');
});

it('serializes ibscbs declaration in dps xml', function () {
    $dpsXml = preg_replace('/<\?xml[^?]*\?>\s*/', '', file_get_contents(__DIR__.'/../../fixtures/xml/dps/ExemploPisZeradoCofinsSobreFaturamentoPreenchido.xml'));
    $wrapper = '<?xml version="1.0"?><NFSe xmlns="http://www.sped.fazenda.gov.br/nfse" versao="1.01"><infNFSe Id="NFS1"><nNFSe>1</nNFSe><cStat>100</cStat><dhProc>2025-01-01T00:00:00-03:00</dhProc>'.$dpsXml.'</infNFSe></NFSe>';
    $nfse = (new NfseXmlParser)->parse($wrapper);
    $rebuilt = (new \Nfse\Xml\DpsXmlBuilder)->build($nfse->infNfse->dps);

    expect($rebuilt)
        ->toContain('<IBSCBS>')
        ->toContain('<finNFSe>0</finNFSe>')
        ->toContain('<cIndOp>100301</cIndOp>')
        ->toContain('<CST>000</CST>')
        ->toContain('<cClassTrib>000001</cClassTrib>');
});

it('maps ibscbs fields to danfse view model', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPisZeradoCofinsSobreFaturamentoPreenchido.xml');
    $nfse = (new NfseXmlParser)->parse($xml);
    $view = (new \Nfse\Danfse\DanfseMapper)->map($nfse);

    expect($view->exibirTributacaoIbsCbs)->toBeTrue()
        ->and($view->finalidade)->toBe('NFS-e regular')
        ->and($view->cstClassTrib)->toBe('000 / 000001')
        ->and($view->baseCalculoIbsCbs)->toBe('R$ 8.740,00')
        ->and($view->valorTotalIbs)->toBe('R$ 8,74')
        ->and($view->valorTotalCbs)->toBe('R$ 78,66')
        ->and($view->valorLiquidoComIbsCbs)->toBe('R$ 9.385,00');
});

it('renders ibscbs section in danfse html', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPisZeradoCofinsSobreFaturamentoPreenchido.xml');
    $data = \Nfse\Danfse\DanfseLayoutData::fromXml($xml);
    $qrCodeDataUri = (new \Nfse\Danfse\Support\CompositeQrCodeGenerator)
        ->generateDataUri((string) $data['qr_code_url'], 150);
    $html = (new \Nfse\Danfse\Renderer\HtmlDanfseRenderer)->render($data, $qrCodeDataUri);

    expect($html)
        ->toContain('Tributação IBS / CBS')
        ->toContain('000 / 000001')
        ->toContain('R$ 8.740,0000');
});
