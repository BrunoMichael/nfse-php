<?php

use Nfse\Http\NfseContext;
use Nfse\Nfse;
use Nfse\Service\DanfseService;
use Nfse\Xml\NfseXmlParser;

it('generates html via danfse service from xml', function () {
    $context = new NfseContext(
        \Nfse\Enums\TipoAmbiente::Homologacao,
        'cert.pfx',
        'password'
    );
    $nfseSdk = new Nfse($context);

    expect($nfseSdk->danfse())->toBeInstanceOf(DanfseService::class);

    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $html = $nfseSdk->danfse()->gerarDeXml($xml);

    expect($html)->toContain('DANFSe v2.0');
});

it('generates html from nfse dto', function () {
    $xml = file_get_contents(__DIR__.'/../../fixtures/xml/ExemploPrestadorPessoaFisica.xml');
    $nfse = (new NfseXmlParser)->parse($xml);

    $context = new NfseContext(
        \Nfse\Enums\TipoAmbiente::Homologacao,
        'cert.pfx',
        'password'
    );

    $html = (new Nfse($context))->danfse()->html($nfse);

    expect($html)->toContain('Prestador do Serviço');
});
