<?php

namespace Nfse\Service;

use Nfse\Http\Client\CncClient;
use Nfse\Http\NfseContext;

class CadastroService
{
    private CncClient $cncClient;

    public function __construct(
        NfseContext $context,
        ?CncClient $cncClient = null,
    ) {
        $this->cncClient = $cncClient ?? new CncClient($context);
    }

    public function consultarContribuinte(string $cpfCnpj): array
    {
        return $this->cncClient->consultarContribuinte($cpfCnpj);
    }

    public function baixarAlteracoes(int $nsu): array
    {
        return $this->cncClient->baixarAlteracoesCadastro($nsu);
    }

    public function atualizarContribuinte(array $dados): array
    {
        return $this->cncClient->atualizarContribuinte($dados);
    }
}
