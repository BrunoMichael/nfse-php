<?php

use Nfse\Danfse\DanfseMapper;
use Nfse\Danfse\DanfseWatermark;
use Nfse\Danfse\Renderer\HtmlDanfseRenderer;
use Nfse\Xml\NfseXmlParser;

it('renders html danfse from nfse fixture', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);
    $view = (new DanfseMapper)->map($nfse);

    $html = (new HtmlDanfseRenderer)->render($view);

    expect($html)
        ->toContain('<!DOCTYPE html>')
        ->toContain('DANFSe v1.0')
        ->toContain('23140031100006672992383000000000004625120025708585')
        ->toContain('NAGILA DE SOUSA FREITAS')
        ->toContain('FUNDO MUNICIPAL DE SAUDE')
        ->toContain('Valor Líquido da NFS-e');
});

it('renders homologacao banner when tpAmb is homologacao', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);
    $nfse->infNfse->dps->infDps->tipoAmbiente = \Nfse\Enums\TipoAmbiente::Homologacao;

    $view = (new DanfseMapper)->map($nfse);
    $html = (new HtmlDanfseRenderer)->render($view);

    expect($html)->toContain('NFS-e SEM VALIDADE JURÍDICA');
});

it('renders watermark markup when cancelada', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);
    $view = (new DanfseMapper)->map($nfse, DanfseWatermark::Cancelada);

    $html = (new HtmlDanfseRenderer)->render($view);

    expect($html)->toContain('watermark-cancelada')
        ->and($html)->toContain('data-watermark="CANCELADA"');
});
