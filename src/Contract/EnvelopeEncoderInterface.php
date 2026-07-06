<?php

namespace Nfse\Contract;

interface EnvelopeEncoderInterface
{
    /**
     * Encapsula XML assinado para transporte (gzip + base64).
     */
    public function encode(string $xml): string;

    /**
     * Decapsula payload recebido da API (base64 + gzip).
     */
    public function decode(string $payload): string;
}
