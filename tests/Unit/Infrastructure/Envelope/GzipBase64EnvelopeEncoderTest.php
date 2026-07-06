<?php

namespace Nfse\Tests\Unit\Infrastructure\Envelope;

use Nfse\Infrastructure\Envelope\GzipBase64EnvelopeEncoder;
use PHPUnit\Framework\TestCase;

class GzipBase64EnvelopeEncoderTest extends TestCase
{
    public function test_encode_and_decode_roundtrip(): void
    {
        $encoder = new GzipBase64EnvelopeEncoder;
        $xml = '<DPS><infDPS Id="DPS123">test</infDPS></DPS>';

        $payload = $encoder->encode($xml);

        $this->assertSame($xml, $encoder->decode($payload));
    }
}
