<?php

namespace Nfse\Infrastructure\Envelope;

use Nfse\Contract\EnvelopeEncoderInterface;
use RuntimeException;

class GzipBase64EnvelopeEncoder implements EnvelopeEncoderInterface
{
    public function encode(string $xml): string
    {
        $compressed = gzencode($xml);

        if ($compressed === false) {
            throw new RuntimeException('Falha ao comprimir XML com gzip.');
        }

        return base64_encode($compressed);
    }

    public function decode(string $payload): string
    {
        $decoded = base64_decode($payload, true);

        if ($decoded === false) {
            throw new RuntimeException('Falha ao decodificar payload base64.');
        }

        $xml = gzdecode($decoded);

        if ($xml === false) {
            throw new RuntimeException('Falha ao descomprimir payload gzip.');
        }

        return $xml;
    }
}
