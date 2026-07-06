<?php

namespace Nfse\Service;

use Closure;
use Nfse\Application\ConsultarNfse;
use Nfse\Application\EmitirNfse;
use Nfse\Dto\Http\ConsultaDpsResponse;
use Nfse\Dto\Nfse\DpsData;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Http\Client\SefinClient;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\NfseContext;
use Nfse\Signer\Certificate;
use Nfse\Signer\SignerInterface;
use Nfse\Signer\XmlSigner;

class EmissaoService
{
    private SefinNacionalInterface $sefinClient;

    private EmitirNfse $emitirNfse;

    private ConsultarNfse $consultarNfse;

    public function __construct(
        private NfseContext $context,
        ?SefinNacionalInterface $sefinClient = null,
        ?Closure $signerFactory = null,
    ) {
        $this->sefinClient = $sefinClient ?? new SefinClient($context);
        $this->emitirNfse = new EmitirNfse($context, $this->sefinClient, signerFactory: $signerFactory);
        $this->consultarNfse = new ConsultarNfse($this->sefinClient);
    }

    public function emitir(DpsData $dps): NfseData
    {
        return $this->emitirNfse->execute($dps);
    }

    public function consultar(string $chave): ?NfseData
    {
        return $this->consultarNfse->execute($chave);
    }

    public function consultarDps(string $idDps): ConsultaDpsResponse
    {
        return $this->sefinClient->consultarDps($idDps);
    }

    public function verificarDps(string $idDps): bool
    {
        return $this->sefinClient->verificarDps($idDps);
    }

    protected function createSigner(Certificate $certificate): SignerInterface
    {
        return new XmlSigner($certificate);
    }
}
