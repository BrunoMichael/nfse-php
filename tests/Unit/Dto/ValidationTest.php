<?php

namespace Nfse\Tests\Unit\Dto;

use Nfse\Dto\Nfse\InfDpsData;

// These tests are now handled by the DpsValidator class instead

it('can instantiate InfDpsData with valid data', function () {
    $data = [
        '@attributes' => ['Id' => 'DPS123'],
        'tpAmb' => 1,
        'dhEmi' => '2023-10-27T10:00:00-03:00',
        'verAplic' => '1.0',
        'serie' => '1',
        'nDPS' => '1001',
        'dCompet' => '2023-10-27',
        'tpEmit' => 1,
        'cLocEmi' => '3550308',
    ];

    $infDps = new InfDpsData($data);

    expect($infDps)->toBeInstanceOf(InfDpsData::class)
        ->and($infDps->id)->toBe('DPS123')
        ->and($infDps->tipoAmbiente)->toBe(\Nfse\Enums\TipoAmbiente::Producao);
});

it('can instantiate InfDpsData with partial data', function () {
    // nullable DTO properties accept null values for nullable properties
    $data = [
        '@attributes' => ['Id' => 'DPS456'],
        'tpAmb' => 2,
        'dhEmi' => '2023-10-27T10:00:00-03:00',
        'verAplic' => '1.0',
        'serie' => '1',
        'nDPS' => '1002',
        'dCompet' => '2023-10-27',
        'tpEmit' => 1,
        'cLocEmi' => '3550308',
        'prest' => null,
        'toma' => null,
    ];

    $infDps = new InfDpsData($data);

    expect($infDps)->toBeInstanceOf(InfDpsData::class)
        ->and($infDps->prestador)->toBeNull()
        ->and($infDps->tomador)->toBeNull();
});
