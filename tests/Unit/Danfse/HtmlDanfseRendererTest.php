<?php

use Nfse\Danfse\DanfseLayoutData;
use Nfse\Danfse\DanfseWatermark;
use Nfse\Danfse\Renderer\HtmlDanfseRenderer;
use Nfse\Danfse\Support\CompositeQrCodeGenerator;

it('renders html danfse from nfse fixture', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $data = DanfseLayoutData::fromXml($xml);
    $qrCodeDataUri = (new CompositeQrCodeGenerator)->generateDataUri((string) $data['qr_code_url'], 150);

    $html = (new HtmlDanfseRenderer)->render($data, $qrCodeDataUri);

    expect($html)
        ->toContain('<!doctype html>')
        ->toContain('DANFSe v2.0')
        ->toContain('23140031100006672992383000000000004625120025708585')
        ->toContain('NAGILA DE SOUSA FREITAS')
        ->toContain('FUNDO MUNICIPAL DE SAUDE')
        ->toContain('Valor Líquido Da NFS-e');
});

it('renders homologacao banner when tpAmb is homologacao', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $data = DanfseLayoutData::fromXml($xml);
    $data['environment_notice'] = 'NFS-e SEM VALIDADE JURÍDICA';
    $qrCodeDataUri = (new CompositeQrCodeGenerator)->generateDataUri((string) $data['qr_code_url'], 150);

    $html = (new HtmlDanfseRenderer)->render($data, $qrCodeDataUri);

    expect($html)->toContain('NFS-e SEM VALIDADE JURÍDICA');
});

it('renders watermark markup when cancelada', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $data = DanfseLayoutData::fromXml($xml, cancelled: true);
    $qrCodeDataUri = (new CompositeQrCodeGenerator)->generateDataUri((string) $data['qr_code_url'], 150);

    $html = (new HtmlDanfseRenderer)->render($data, $qrCodeDataUri);

    expect($html)->toContain('class="watermark"')
        ->and($html)->toContain('CANCELADA');
});

it('embeds official nfse logo when asset is present', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $data = DanfseLayoutData::fromXml($xml);
    $qrCodeDataUri = (new CompositeQrCodeGenerator)->generateDataUri((string) $data['qr_code_url'], 150);

    $html = (new HtmlDanfseRenderer)->render($data, $qrCodeDataUri);

    expect($html)->toContain('data:image/png;base64,');
});
