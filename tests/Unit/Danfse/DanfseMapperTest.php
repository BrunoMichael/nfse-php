<?php

use Nfse\Danfse\DanfseMapper;
use Nfse\Danfse\DanfseWatermark;
use Nfse\Enums\CodigoStatus;
use Nfse\Xml\NfseXmlParser;

it('maps nfse fixture to danfse view model', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);

    $view = (new DanfseMapper)->map($nfse);

    expect($view->numeroNfse)->toBe('46')
        ->and($view->chaveAcesso)->toBe('23140031100006672992383000000000004625120025708585')
        ->and($view->urlConsulta)->toContain('chave='.$view->chaveAcesso)
        ->and($view->qrCodeDataUri)->toStartWith('data:')
        ->and($view->prestadorNome)->toContain('NAGILA')
        ->and($view->tomadorIdentificado)->toBeTrue()
        ->and($view->tomadorNome)->toContain('FUNDO MUNICIPAL')
        ->and($view->valorLiquidoNfse)->toBe('1.757,50')
        ->and($view->exibirTributacaoIssqn)->toBeTrue();
});

it('detects substituída watermark from codigo status', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);
    $nfse->infNfse->codigoStatus = CodigoStatus::NfseSubstituicaoGerada;

    $view = (new DanfseMapper)->map($nfse);

    expect($view->marcaDagua)->toBe(DanfseWatermark::Substituída);
});

it('allows explicit watermark override', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);

    $view = (new DanfseMapper)->map($nfse, DanfseWatermark::Cancelada);

    expect($view->marcaDagua)->toBe(DanfseWatermark::Cancelada);
});
