<?php

namespace Nfse\Application;

use Closure;
use Nfse\Application\Support\CertificateFactory;
use Nfse\Contract\EnvelopeEncoderInterface;
use Nfse\Dto\Nfse\DpsData;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Exception\ValidationException;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\Exceptions\NfseApiException;
use Nfse\Http\NfseContext;
use Nfse\Infrastructure\Envelope\GzipBase64EnvelopeEncoder;
use Nfse\Signer\Certificate;
use Nfse\Signer\SignerInterface;
use Nfse\Signer\XmlSigner;
use Nfse\Validator\DpsValidator;
use Nfse\Xml\DpsXmlBuilder;
use Nfse\Xml\NfseXmlParser;

class EmitirNfse
{
    private EnvelopeEncoderInterface $envelope;

    public function __construct(
        private NfseContext $context,
        private SefinNacionalInterface $sefin,
        ?EnvelopeEncoderInterface $envelope = null,
        private ?DpsXmlBuilder $xmlBuilder = null,
        private ?NfseXmlParser $parser = null,
        private ?DpsValidator $validator = null,
        private ?Closure $signerFactory = null,
    ) {
        $this->envelope = $envelope ?? new GzipBase64EnvelopeEncoder;
        $this->xmlBuilder ??= new DpsXmlBuilder;
        $this->parser ??= new NfseXmlParser;
        $this->validator ??= new DpsValidator;
    }

    public function execute(DpsData $dps): NfseData
    {
        if ($this->context->validateDpsBeforeEmit) {
            $validation = $this->validator->validate($dps);

            if (! $validation->isValid) {
                throw new ValidationException($validation->getErrors());
            }
        }

        $xml = $this->xmlBuilder->build($dps);
        $cert = CertificateFactory::fromContext($this->context);
        $signer = $this->createSigner($cert);
        $signedXml = $signer->sign($xml, 'infDPS');
        $payload = $this->envelope->encode($signedXml);

        $response = $this->sefin->emitirNfse($payload);

        if (! empty($response->erros)) {
            $msg = 'Erro na emissão: '.json_encode($response->erros);
            throw NfseApiException::responseError($msg, 0, null, $response->erros);
        }

        if (! $response->nfseXmlGZipB64) {
            throw NfseApiException::responseError('Resposta sem XML da NFS-e.');
        }

        $nfseXml = $this->envelope->decode($response->nfseXmlGZipB64);

        return $this->parser->parse($nfseXml);
    }

    private function createSigner(Certificate $certificate): SignerInterface
    {
        if ($this->signerFactory !== null) {
            return ($this->signerFactory)($certificate);
        }

        return new XmlSigner($certificate);
    }
}
