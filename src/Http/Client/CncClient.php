<?php

namespace Nfse\Http\Client;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Nfse\Enums\TipoAmbiente;
use Nfse\Http\Exceptions\NfseApiException;
use Nfse\Http\NfseContext;

class CncClient extends AbstractMutualTlsClient
{
    private const URL_PRODUCTION = 'https://adn.nfse.gov.br/cnc';

    private const URL_HOMOLOGATION = 'https://adn.producaorestrita.nfse.gov.br/cnc';

    protected function resolveBaseUrl(): string
    {
        return $this->context->ambiente === TipoAmbiente::Producao
            ? self::URL_PRODUCTION
            : self::URL_HOMOLOGATION;
    }

    private function get(string $endpoint): array
    {
        try {
            $response = $this->httpClient->get($endpoint);
            $content = $response->getBody()->getContents();
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw NfseApiException::responseError('Resposta inválida (não é JSON): '.$content);
            }

            return $decoded;
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    private function post(string $endpoint, array $data): array
    {
        try {
            $response = $this->httpClient->post($endpoint, [
                RequestOptions::JSON => $data,
            ]);
            $content = $response->getBody()->getContents();
            $decoded = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw NfseApiException::responseError('Resposta inválida (não é JSON): '.$content);
            }

            return $decoded;
        } catch (GuzzleException $e) {
            $this->handleException($e);
        }
    }

    public function consultarContribuinte(string $cpfCnpj): array
    {
        return $this->get("/consulta/cad/{$cpfCnpj}");
    }

    public function baixarAlteracoesCadastro(int $nsu): array
    {
        return $this->get("/municipio/cad/{$nsu}");
    }

    public function atualizarContribuinte(array $dados): array
    {
        return $this->post('', $dados);
    }

    /**
     * @param  \Exception|GuzzleException  $e
     * @return mixed
     *
     * @throws NfseApiException
     */
    private function handleException(\Exception|GuzzleException $e)
    {
        $errorBody = '';
        if ($e instanceof \GuzzleHttp\Exception\RequestException && $e->hasResponse()) {
            $errorBody = $e->getResponse()->getBody()->getContents();
        }

        $parsedErrors = [];
        if ($errorBody) {
            $decoded = json_decode($errorBody, true);
            if (is_array($decoded) && isset($decoded['erros']) && is_array($decoded['erros'])) {
                $parsedErrors = $this->mapMensagens($decoded['erros']);
            }
        }

        throw NfseApiException::requestError(
            $e->getMessage().($errorBody ? "\nResposta: ".$errorBody : ''),
            $e->getCode(),
            $errorBody ?: null,
            $parsedErrors
        );
    }
}
