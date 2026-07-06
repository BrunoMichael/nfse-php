<?php

namespace Nfse;

use Nfse\Http\Client\AdnClient;
use Nfse\Http\NfseContext;
use Nfse\Service\CadastroService;
use Nfse\Service\ContribuinteService;
use Nfse\Service\DanfseService;
use Nfse\Service\DistribuicaoService;
use Nfse\Service\EmissaoService;
use Nfse\Service\EventosService;
use Nfse\Service\MunicipioService;
use Nfse\Service\ParametrosService;
use Nfse\XmlGateway;

class Nfse
{
    public function __construct(private NfseContext $context) {}

    public function emissao(): EmissaoService
    {
        return new EmissaoService($this->context);
    }

    public function eventos(): EventosService
    {
        return new EventosService($this->context);
    }

    public function distribuicao(): DistribuicaoService
    {
        return new DistribuicaoService($this->context);
    }

    public function parametros(): ParametrosService
    {
        return new ParametrosService($this->context);
    }

    public function cadastro(): CadastroService
    {
        return new CadastroService($this->context);
    }

    public function sefin(): EmissaoService
    {
        return $this->emissao();
    }

    public function adn(): DistribuicaoService
    {
        return $this->distribuicao();
    }

    public function cnc(): CadastroService
    {
        return $this->cadastro();
    }

    public function xml(): XmlGateway
    {
        return new XmlGateway($this->context);
    }

    public function danfse(): DanfseService
    {
        return new DanfseService($this->context);
    }

    /**
     * @deprecated Use {@see emissao()}, {@see eventos()}, {@see distribuicao()} ou {@see parametros()}.
     */
    public function contribuinte(): ContribuinteService
    {
        return new ContribuinteService($this->context);
    }

    /**
     * @deprecated Use {@see distribuicao()}, {@see parametros()} ou {@see cadastro()}.
     */
    public function municipio(): MunicipioService
    {
        return new MunicipioService($this->context);
    }

    /**
     * @deprecated A API ADN de geração do DANFSe foi descontinuada. Use um gerador local conforme NT-008.
     */
    public function downloadDanfse(string $chaveAcesso): string
    {
        return (new AdnClient($this->context))->obterDanfse($chaveAcesso);
    }
}
