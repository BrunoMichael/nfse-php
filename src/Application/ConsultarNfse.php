<?php

namespace Nfse\Application;

use Nfse\Contract\EnvelopeEncoderInterface;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\Exceptions\NfseApiException;
use Nfse\Infrastructure\Envelope\GzipBase64EnvelopeEncoder;
use Nfse\Xml\NfseXmlParser;

class ConsultarNfse
{
    private EnvelopeEncoderInterface $envelope;

    public function __construct(
        private SefinNacionalInterface $sefin,
        ?EnvelopeEncoderInterface $envelope = null,
        private ?NfseXmlParser $parser = null,
    ) {
        $this->envelope = $envelope ?? new GzipBase64EnvelopeEncoder;
        $this->parser ??= new NfseXmlParser;
    }

    public function execute(string $chave): ?NfseData
    {
        try {
            $response = $this->sefin->consultarNfse($chave);
        } catch (NfseApiException $e) {
            return null;
        }

        if (! $response->nfseXmlGZipB64) {
            return null;
        }

        $nfseXml = $this->envelope->decode($response->nfseXmlGZipB64);

        return $this->parser->parse($nfseXml);
    }
}
