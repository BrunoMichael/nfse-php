<?php

namespace Nfse\Tests\Unit\Application;

use Nfse\Application\EmitirNfse;
use Nfse\Dto\Nfse\DpsData;
use Nfse\Enums\TipoAmbiente;
use Nfse\Exception\ValidationException;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\NfseContext;
use Nfse\Support\IdGenerator;
use PHPUnit\Framework\TestCase;

class EmitirNfseTest extends TestCase
{
    public function test_throws_validation_exception_when_prestador_is_missing(): void
    {
        $context = new NfseContext(
            TipoAmbiente::Homologacao,
            __DIR__.'/../../fixtures/certs/test.pfx',
            '1234',
            validateDpsBeforeEmit: true,
        );

        $sefin = $this->createMock(SefinNacionalInterface::class);
        $sefin->expects($this->never())->method('emitirNfse');

        $idDps = IdGenerator::generateDpsId('12345678000199', '3550308', '1', '1');
        $dps = new DpsData([
            '@attributes' => ['versao' => '1.00'],
            'infDPS' => [
                '@attributes' => ['Id' => $idDps],
                'tpAmb' => 2,
                'dhEmi' => '2023-10-27T10:00:00',
                'verAplic' => '1.0',
                'serie' => '1',
                'nDPS' => '1',
                'dCompet' => '2023-10-27',
                'tpEmit' => 1,
                'cLocEmi' => '3550308',
            ],
        ]);

        $this->expectException(ValidationException::class);

        (new EmitirNfse($context, $sefin))->execute($dps);
    }
}
