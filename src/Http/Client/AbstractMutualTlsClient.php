<?php

namespace Nfse\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Nfse\Dto\Http\MensagemProcessamentoDto;
use Nfse\Http\NfseContext;

abstract class AbstractMutualTlsClient
{
    protected Client $httpClient;

    protected ?string $tempCertFile = null;

    public function __construct(protected NfseContext $context)
    {
        $this->httpClient = $this->createHttpClient();
    }

    public function __destruct()
    {
        if ($this->tempCertFile !== null && file_exists($this->tempCertFile)) {
            unlink($this->tempCertFile);
        }
    }

    abstract protected function resolveBaseUrl(): string;

    protected function createHttpClient(): Client
    {
        return new Client([
            'base_uri' => rtrim($this->resolveBaseUrl(), '/').'/',
            'curl' => [
                CURLOPT_SSLCERTTYPE => 'P12',
                CURLOPT_SSLCERT => $this->resolveCertificatePath(),
                CURLOPT_SSLCERTPASSWD => $this->context->certificatePassword,
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT => 60,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_SSL_VERIFYHOST => $this->context->verifySsl ? 2 : 0,
                CURLOPT_SSL_VERIFYPEER => $this->context->verifySsl ? 1 : 0,
            ],
            RequestOptions::HEADERS => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    protected function resolveCertificatePath(): string
    {
        if ($this->context->certificatePath !== null) {
            return $this->context->certificatePath;
        }

        $this->tempCertFile = tempnam(sys_get_temp_dir(), 'nfse_cert_');
        file_put_contents($this->tempCertFile, $this->context->certificateContent);

        return $this->tempCertFile;
    }

    /**
     * @return MensagemProcessamentoDto[]
     */
    protected function mapMensagens(array $mensagens): array
    {
        return array_map(fn ($m) => new MensagemProcessamentoDto(
            mensagem: $m['mensagem'] ?? $m['Mensagem'] ?? null,
            parametros: $m['parametros'] ?? $m['Parametros'] ?? null,
            codigo: $m['codigo'] ?? $m['Codigo'] ?? null,
            descricao: $m['descricao'] ?? $m['Descricao'] ?? null,
            complemento: $m['complemento'] ?? $m['Complemento'] ?? null
        ), $mensagens);
    }
}
