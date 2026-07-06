<?php

namespace Nfse\Application;

use Closure;
use Nfse\Application\Support\CertificateFactory;
use Nfse\Contract\EnvelopeEncoderInterface;
use Nfse\Dto\Http\RegistroEventoResponse;
use Nfse\Dto\Nfse\PedRegEventoData;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\NfseContext;
use Nfse\Infrastructure\Envelope\GzipBase64EnvelopeEncoder;
use Nfse\Signer\Certificate;
use Nfse\Signer\SignerInterface;
use Nfse\Signer\XmlSigner;
use Nfse\Xml\EventosXmlBuilder;

class RegistrarEvento
{
    private EnvelopeEncoderInterface $envelope;

    public function __construct(
        private NfseContext $context,
        private SefinNacionalInterface $sefin,
        ?EnvelopeEncoderInterface $envelope = null,
        private ?EventosXmlBuilder $xmlBuilder = null,
        private ?Closure $signerFactory = null,
    ) {
        $this->envelope = $envelope ?? new GzipBase64EnvelopeEncoder;
        $this->xmlBuilder ??= new EventosXmlBuilder;
    }

    public function execute(PedRegEventoData $evento): RegistroEventoResponse
    {
        $xml = $this->xmlBuilder->buildPedRegEvento($evento);
        $cert = CertificateFactory::fromContext($this->context);
        $signer = $this->createSigner($cert);
        $signedXml = $signer->sign($xml, 'infPedReg');
        $payload = $this->envelope->encode($signedXml);

        return $this->sefin->registrarEvento($evento->infPedReg->chaveNfse, $payload);
    }

    private function createSigner(Certificate $certificate): SignerInterface
    {
        if ($this->signerFactory !== null) {
            return ($this->signerFactory)($certificate);
        }

        return new XmlSigner($certificate);
    }
}
