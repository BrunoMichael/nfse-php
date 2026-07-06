<?php

use Nfse\Dto\Nfse\InfPedRegData;
use Nfse\Dto\Nfse\PedRegEventoData;
use Nfse\Enums\DocumentType;
use Nfse\Enums\SchemaVersion;
use Nfse\Infrastructure\Xml\XmlSerializerRegistry;
use Nfse\Xml\EventosXmlBuilder;
use Nfse\XmlGateway;

const CHAVE_NFSE_TESTE = '12345678901234567890123456789012345678901234567890';

function infPedRegBase(array $eventPayload): InfPedRegData
{
    return new InfPedRegData(array_merge([
        'tpAmb' => 2,
        'verAplic' => '1.0',
        'dhEvento' => '2025-01-01T12:00:00-03:00',
        'CNPJAutor' => '12345678000199',
        'chNFSe' => CHAVE_NFSE_TESTE,
    ], $eventPayload));
}

function buildEventoXml(array $eventPayload, string $tipoEvento = '101101'): string
{
    $inf = infPedRegBase($eventPayload);
    $inf->tipoEvento = $tipoEvento;

    return (new EventosXmlBuilder)->buildPedRegEvento(new PedRegEventoData(['infPedReg' => $inf]));
}

it('builds e105102 cancelamento por substituicao', function () {
    $xml = buildEventoXml([
        'e105102' => [
            'xDesc' => 'Cancelamento de NFS-e por Substituicao',
            'cMotivo' => '1',
            'xMotivo' => 'Substituicao',
            'chSubstituta' => CHAVE_NFSE_TESTE,
        ],
    ], '105102');

    expect($xml)->toContain('<e105102>')
        ->and($xml)->toContain('<chSubstituta>'.CHAVE_NFSE_TESTE.'</chSubstituta>')
        ->and($xml)->toContain('Id="PRE'.CHAVE_NFSE_TESTE.'105102"');
});

it('builds e101103 solicitacao analise fiscal', function () {
    $xml = buildEventoXml([
        'e101103' => [
            'xDesc' => 'Solicitacao de Analise Fiscal para Cancelamento de NFS-e',
            'cMotivo' => '1',
            'xMotivo' => 'Analise',
        ],
    ], '101103');

    expect($xml)->toContain('<e101103>')
        ->and($xml)->toContain('<xMotivo>Analise</xMotivo>');
});

it('builds e105104 cancelamento deferido por analise fiscal', function () {
    $xml = buildEventoXml([
        'e105104' => [
            'xDesc' => 'Cancelamento de NFS-e Deferido por Análise Fiscal',
            'CPFAgTrib' => '11122233344',
            'nProcAdm' => 'PROC123',
            'cMotivo' => '1',
            'xMotivo' => 'Deferido',
        ],
    ], '105104');

    expect($xml)->toContain('<e105104>')
        ->and($xml)->toContain('<CPFAgTrib>11122233344</CPFAgTrib>')
        ->and($xml)->toContain('<nProcAdm>PROC123</nProcAdm>');
});

it('builds e105105 cancelamento indeferido por analise fiscal', function () {
    $xml = buildEventoXml([
        'e105105' => [
            'xDesc' => 'Cancelamento de NFS-e Indeferido por Análise Fiscal',
            'CPFAgTrib' => '11122233344',
            'cMotivo' => '2',
            'xMotivo' => 'Indeferido',
        ],
    ], '105105');

    expect($xml)->toContain('<e105105>');
});

it('builds confirmacao events', function (string $tag, string $tipo, string $descricao) {
    $xml = buildEventoXml([$tag => ['xDesc' => $descricao]], $tipo);

    expect($xml)->toContain("<{$tag}>")
        ->and($xml)->toContain("<xDesc>{$descricao}</xDesc>");
})->with([
    ['e202201', '202201', 'Confirmação do Prestador'],
    ['e203202', '203202', 'Confirmação do Tomador'],
    ['e204203', '204203', 'Confirmação do Intermediário'],
    ['e205204', '205204', 'Confirmação Tácita'],
]);

it('builds rejeicao events with infRej', function (string $tag, string $tipo) {
    $xml = buildEventoXml([
        $tag => [
            'xDesc' => 'Rejeição',
            'infRej' => [
                'cMotivo' => '9',
                'xMotivo' => 'Outros',
            ],
        ],
    ], $tipo);

    expect($xml)->toContain("<{$tag}>")
        ->and($xml)->toContain('<infRej>')
        ->and($xml)->toContain('<cMotivo>9</cMotivo>');
})->with([
    ['e202205', '202205'],
    ['e203206', '203206'],
    ['e204207', '204207'],
]);

it('builds e205208 anulacao de rejeicao', function () {
    $xml = buildEventoXml([
        'e205208' => [
            'xDesc' => 'Anulação da Rejeição',
            'infAnRej' => [
                'CPFAgTrib' => '11122233344',
                'idEvManifRej' => 'EVT123',
                'xMotivo' => 'Anulacao',
            ],
        ],
    ], '205208');

    expect($xml)->toContain('<e205208>')
        ->and($xml)->toContain('<infAnRej>')
        ->and($xml)->toContain('<idEvManifRej>EVT123</idEvManifRej>');
});

it('builds e305101 cancelamento por oficio', function () {
    $xml = buildEventoXml([
        'e305101' => [
            'xDesc' => 'Cancelamento de NFS-e por Ofício',
            'CPFAgTrib' => '11122233344',
            'nProcAdm' => 'PROC999',
            'xProcAdm' => 'Motivo oficio',
        ],
    ], '305101');

    expect($xml)->toContain('<e305101>')
        ->and($xml)->toContain('<xProcAdm>Motivo oficio</xProcAdm>');
});

it('builds e305102 bloqueio por oficio', function () {
    $xml = buildEventoXml([
        'e305102' => [
            'xDesc' => 'Bloqueio de NFS-e por Ofício',
            'CPFAgTrib' => '11122233344',
            'xMotivo' => 'Bloqueio',
            'codEvento' => '202205',
        ],
    ], '305102');

    expect($xml)->toContain('<e305102>')
        ->and($xml)->toContain('<codEvento>202205</codEvento>');
});

it('builds e305103 desbloqueio por oficio', function () {
    $xml = buildEventoXml([
        'e305103' => [
            'xDesc' => 'Desbloqueio de NFS-e por Ofício',
            'CPFAgTrib' => '11122233344',
            'idBloqOfic' => 'BLOQ123',
        ],
    ], '305103');

    expect($xml)->toContain('<e305103>')
        ->and($xml)->toContain('<idBloqOfic>BLOQ123</idBloqOfic>');
});

it('serializes pedRegEvento via XmlGateway', function () {
    $context = new \Nfse\Http\NfseContext(
        \Nfse\Enums\TipoAmbiente::Homologacao,
        __DIR__.'/../../fixtures/certs/test.pfx',
        '1234'
    );

    $inf = infPedRegBase([
        'e101101' => [
            'xDesc' => 'Cancelamento de NFS-e',
            'cMotivo' => '1',
            'xMotivo' => 'Via gateway',
        ],
    ]);

    $pedido = new PedRegEventoData(['infPedReg' => $inf, 'versao' => '1.01']);

    $xml = (new XmlGateway($context))
        ->document(DocumentType::PedRegEvento)
        ->version(SchemaVersion::V1_01)
        ->serialize($pedido);

    expect($xml)->toContain('versao="1.01"')
        ->and($xml)->toContain('<e101101>');
});

it('detects schema version from xml attribute', function () {
    $registry = new XmlSerializerRegistry;
    $version = $registry->detectVersion('<DPS versao="1.00"><infDPS/></DPS>');

    expect($version)->toBe(SchemaVersion::V1_00);
});
