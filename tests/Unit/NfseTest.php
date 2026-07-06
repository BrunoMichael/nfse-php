<?php

namespace Nfse\Tests\Unit;

use Nfse\Http\NfseContext;
use Nfse\Nfse;
use Nfse\Service\CadastroService;
use Nfse\Service\ContribuinteService;
use Nfse\Service\DanfseService;
use Nfse\Service\DistribuicaoService;
use Nfse\Service\EmissaoService;
use Nfse\Service\EventosService;
use Nfse\Service\MunicipioService;
use Nfse\Service\ParametrosService;
use PHPUnit\Framework\TestCase;

class NfseTest extends TestCase
{
    public function test_can_instantiate_legacy_services()
    {
        $context = new NfseContext(
            \Nfse\Enums\TipoAmbiente::Homologacao,
            'cert.pfx',
            'password'
        );
        $nfse = new Nfse($context);

        $this->assertInstanceOf(ContribuinteService::class, $nfse->contribuinte());
        $this->assertInstanceOf(MunicipioService::class, $nfse->municipio());
    }

    public function test_can_instantiate_v2_services()
    {
        $context = new NfseContext(
            \Nfse\Enums\TipoAmbiente::Homologacao,
            'cert.pfx',
            'password'
        );
        $nfse = new Nfse($context);

        $this->assertInstanceOf(EmissaoService::class, $nfse->emissao());
        $this->assertInstanceOf(EventosService::class, $nfse->eventos());
        $this->assertInstanceOf(DistribuicaoService::class, $nfse->distribuicao());
        $this->assertInstanceOf(ParametrosService::class, $nfse->parametros());
        $this->assertInstanceOf(CadastroService::class, $nfse->cadastro());
        $this->assertInstanceOf(DanfseService::class, $nfse->danfse());
        $this->assertInstanceOf(EmissaoService::class, $nfse->sefin());
        $this->assertInstanceOf(DistribuicaoService::class, $nfse->adn());
        $this->assertInstanceOf(CadastroService::class, $nfse->cnc());
        $this->assertInstanceOf(\Nfse\XmlGateway::class, $nfse->xml());
    }
}
