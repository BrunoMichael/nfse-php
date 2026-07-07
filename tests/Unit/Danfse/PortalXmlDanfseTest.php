<?php

use Nfse\Danfse\DanfseLayoutData;
use Nfse\Danfse\DanfseWatermark;
use Nfse\Danfse\Renderer\HtmlDanfseRenderer;
use Nfse\Danfse\Support\CompositeQrCodeGenerator;
use Nfse\Service\DanfseService;
use Nfse\Http\NfseContext;

it('renders portal montes claros xml with nt-008 layout', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPortalMontesClaros.xml');
    $data = DanfseLayoutData::fromXml($xml);
    $qr = (new CompositeQrCodeGenerator)->generateDataUri((string) $data['qr_code_url'], 150);
    $html = (new HtmlDanfseRenderer)->render($data, $qr);

    expect($html)
        ->toContain('DANFSe v2.0')
        ->toContain('31433022254614603000102000000000003026032643854675')
        ->toContain('ALMEIDA E GONCALVES INFORMATICA')
        ->toContain('POSTO PRODUCAO LTDA')
        ->toContain('Montes Claros / MG')
        ->toContain('R$ 230,0000')
        ->toContain('DESTINATÁRIO DA OPERAÇÃO NÃO IDENTIFICADO NA NFS-e')
        ->toContain('Municipais: 6,0000%');
});

it('renders cancelada watermark on same nt-008 layout', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPortalMontesClaros.xml');
    $data = DanfseLayoutData::fromXml($xml, cancelled: true);
    $qr = (new CompositeQrCodeGenerator)->generateDataUri((string) $data['qr_code_url'], 150);
    $html = (new HtmlDanfseRenderer)->render($data, $qr);

    expect($html)
        ->toContain('class="watermark"')
        ->toContain('CANCELADA')
        ->toContain('Cancelada');
});

it('renders homologacao notice on same nt-008 layout', function () {
    $xml = str_replace('<tpAmb>1</tpAmb>', '<tpAmb>2</tpAmb>', file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPortalMontesClaros.xml'));
    $context = new NfseContext(\Nfse\Enums\TipoAmbiente::Homologacao, 'cert.pfx', 'password');
    $html = (new DanfseService($context))->gerarDeXml($xml);

    expect($html)->toContain('NFS-e SEM VALIDADE JURÍDICA');
});

it('renders substituida watermark via service watermark enum', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPortalMontesClaros.xml');
    $context = new NfseContext(\Nfse\Enums\TipoAmbiente::Producao, 'cert.pfx', 'password');
    $html = (new DanfseService($context))->gerarDeXml($xml, watermark: DanfseWatermark::Substituída);

    expect($html)->toContain('SUBSTITUÍDA');
});
