<?php

namespace Nfse\Service;

use Closure;
use Nfse\Application\RegistrarEvento;
use Nfse\Dto\Http\RegistroEventoResponse;
use Nfse\Dto\Nfse\PedRegEventoData;
use Nfse\Http\Client\AdnClient;
use Nfse\Http\Client\SefinClient;
use Nfse\Http\Contracts\SefinNacionalInterface;
use Nfse\Http\NfseContext;

class EventosService
{
    private SefinNacionalInterface $sefinClient;

    private AdnClient $adnClient;

    private RegistrarEvento $registrarEvento;

    public function __construct(
        private NfseContext $context,
        ?SefinNacionalInterface $sefinClient = null,
        ?AdnClient $adnClient = null,
        ?Closure $signerFactory = null,
    ) {
        $this->sefinClient = $sefinClient ?? new SefinClient($context);
        $this->adnClient = $adnClient ?? new AdnClient($context);
        $this->registrarEvento = new RegistrarEvento($context, $this->sefinClient, signerFactory: $signerFactory);
    }

    public function registrar(PedRegEventoData $evento): RegistroEventoResponse
    {
        $this->syncTipoEvento($evento);

        return $this->registrarEvento->execute($evento);
    }

    public function cancelar(PedRegEventoData $evento): RegistroEventoResponse
    {
        $evento->infPedReg->tipoEvento = '101101';

        return $this->registrar($evento);
    }

    public function registrarPayload(string $chaveAcesso, string $eventoXmlGZipB64): RegistroEventoResponse
    {
        return $this->sefinClient->registrarEvento($chaveAcesso, $eventoXmlGZipB64);
    }

    public function consultar(string $chaveAcesso, int $tipoEvento, int $numSeqEvento): RegistroEventoResponse
    {
        return $this->sefinClient->consultarEvento($chaveAcesso, $tipoEvento, $numSeqEvento);
    }

    public function listar(string $chaveAcesso, ?int $tipoEvento = null): array
    {
        if ($tipoEvento) {
            return $this->sefinClient->listarEventosPorTipo($chaveAcesso, $tipoEvento);
        }

        return $this->sefinClient->listarEventos($chaveAcesso);
    }

    public function listarAdn(string $chaveAcesso): array
    {
        return $this->adnClient->consultarEventosContribuinte($chaveAcesso);
    }

    private function syncTipoEvento(PedRegEventoData $evento): void
    {
        if ($evento->infPedReg === null) {
            return;
        }

        $map = [
            'e101101' => '101101',
            'e105102' => '105102',
            'e101103' => '101103',
            'e105104' => '105104',
            'e105105' => '105105',
            'e202201' => '202201',
            'e203202' => '203202',
            'e204203' => '204203',
            'e205204' => '205204',
            'e202205' => '202205',
            'e203206' => '203206',
            'e204207' => '204207',
            'e205208' => '205208',
            'e305101' => '305101',
            'e305102' => '305102',
            'e305103' => '305103',
        ];

        foreach ($map as $property => $code) {
            if ($evento->infPedReg->{$property} !== null) {
                $evento->infPedReg->tipoEvento = $code;

                return;
            }
        }
    }
}
