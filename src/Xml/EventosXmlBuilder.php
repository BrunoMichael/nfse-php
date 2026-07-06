<?php

namespace Nfse\Xml;

use DOMDocument;
use DOMElement;
use Nfse\Dto\Nfse\AnaliseFiscalData;
use Nfse\Dto\Nfse\AnaliseFiscalSolicitacaoData;
use Nfse\Dto\Nfse\AnulacaoRejeicaoData;
use Nfse\Dto\Nfse\BloqueioPorOficioData;
use Nfse\Dto\Nfse\CancelamentoData;
use Nfse\Dto\Nfse\CancelamentoPorOficioData;
use Nfse\Dto\Nfse\CancelamentoSubstituicaoData;
use Nfse\Dto\Nfse\ConfirmacaoIntermediarioData;
use Nfse\Dto\Nfse\ConfirmacaoPrestadorData;
use Nfse\Dto\Nfse\ConfirmacaoTacitaData;
use Nfse\Dto\Nfse\ConfirmacaoTomadorData;
use Nfse\Dto\Nfse\DesbloqueioPorOficioData;
use Nfse\Dto\Nfse\InfPedRegData;
use Nfse\Dto\Nfse\PedRegEventoData;
use Nfse\Dto\Nfse\RejeicaoIntermediarioData;
use Nfse\Dto\Nfse\RejeicaoPrestadorData;
use Nfse\Dto\Nfse\RejeicaoTomadorData;
use InvalidArgumentException;

class EventosXmlBuilder
{
    private DOMDocument $dom;

    public function buildPedRegEvento(PedRegEventoData $data): string
    {
        if ($data->infPedReg === null) {
            throw new InvalidArgumentException('infPedReg é obrigatório para gerar o pedRegEvento.');
        }

        $this->dom = new DOMDocument('1.0', 'UTF-8');
        $this->dom->formatOutput = false;

        $root = $this->dom->createElementNS('http://www.sped.fazenda.gov.br/nfse', 'pedRegEvento');
        $root->setAttribute('versao', (string) $data->versao);
        $this->dom->appendChild($root);

        $inf = $this->dom->createElement('infPedReg');

        $ch = $data->infPedReg->chaveNfse;
        $tipo = $data->infPedReg->tipoEvento;
        $id = "PRE{$ch}{$tipo}";
        $inf->setAttribute('Id', $id);

        $this->appendElement($inf, 'tpAmb', $data->infPedReg->tipoAmbiente);
        $this->appendElement($inf, 'verAplic', $data->infPedReg->versaoAplicativo);
        $this->appendElement($inf, 'dhEvento', $data->infPedReg->dataHoraEvento);

        if ($data->infPedReg->cnpjAutor) {
            $this->appendElement($inf, 'CNPJAutor', $data->infPedReg->cnpjAutor);
        }
        if ($data->infPedReg->cpfAutor) {
            $this->appendElement($inf, 'CPFAutor', $data->infPedReg->cpfAutor);
        }

        $this->appendElement($inf, 'chNFSe', $data->infPedReg->chaveNfse);

        $this->appendEventPayload($inf, $data->infPedReg);

        $root->appendChild($inf);

        $xml = $this->dom->saveXML($this->dom->documentElement, LIBXML_NOXMLDECL);

        return str_replace(["\n", "\r", "\t"], '', $xml);
    }

    private function appendEventPayload(DOMElement $inf, InfPedRegData $data): void
    {
        if ($data->e101101 !== null) {
            $this->buildE101101($inf, $data->e101101);

            return;
        }

        if ($data->e105102 !== null) {
            $this->buildE105102($inf, $data->e105102);

            return;
        }

        if ($data->e101103 !== null) {
            $this->buildE101103($inf, $data->e101103);

            return;
        }

        if ($data->e105104 !== null) {
            $this->buildE105104($inf, $data->e105104);

            return;
        }

        if ($data->e105105 !== null) {
            $this->buildE105105($inf, $data->e105105);

            return;
        }

        if ($data->e202201 !== null) {
            $this->buildE202201($inf, $data->e202201);

            return;
        }

        if ($data->e203202 !== null) {
            $this->buildE203202($inf, $data->e203202);

            return;
        }

        if ($data->e204203 !== null) {
            $this->buildE204203($inf, $data->e204203);

            return;
        }

        if ($data->e205204 !== null) {
            $this->buildE205204($inf, $data->e205204);

            return;
        }

        if ($data->e202205 !== null) {
            $this->buildE202205($inf, $data->e202205);

            return;
        }

        if ($data->e203206 !== null) {
            $this->buildE203206($inf, $data->e203206);

            return;
        }

        if ($data->e204207 !== null) {
            $this->buildE204207($inf, $data->e204207);

            return;
        }

        if ($data->e205208 !== null) {
            $this->buildE205208($inf, $data->e205208);

            return;
        }

        if ($data->e305101 !== null) {
            $this->buildE305101($inf, $data->e305101);

            return;
        }

        if ($data->e305102 !== null) {
            $this->buildE305102($inf, $data->e305102);

            return;
        }

        if ($data->e305103 !== null) {
            $this->buildE305103($inf, $data->e305103);

            return;
        }

        throw new InvalidArgumentException('Nenhum tipo de evento informado em infPedReg.');
    }

    private function buildE101101(DOMElement $parent, CancelamentoData $data): void
    {
        $e = $this->dom->createElement('e101101');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'cMotivo', $data->codigoMotivo);
        $this->appendElement($e, 'xMotivo', $data->motivo);
        $parent->appendChild($e);
    }

    private function buildE105102(DOMElement $parent, CancelamentoSubstituicaoData $data): void
    {
        $e = $this->dom->createElement('e105102');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'cMotivo', $data->codigoMotivo);
        $this->appendElement($e, 'xMotivo', $data->descricaoMotivo);
        $this->appendElement($e, 'chSubstituta', $data->chaveNfseSubstituta);
        $parent->appendChild($e);
    }

    private function buildE101103(DOMElement $parent, AnaliseFiscalSolicitacaoData $data): void
    {
        $e = $this->dom->createElement('e101103');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'cMotivo', $data->codigoMotivo);
        $this->appendElement($e, 'xMotivo', $data->motivo);
        $parent->appendChild($e);
    }

    private function buildE105104(DOMElement $parent, AnaliseFiscalData $data): void
    {
        $e = $this->dom->createElement('e105104');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'CPFAgTrib', $data->cpfAgenteTributario);
        $this->appendElement($e, 'nProcAdm', $data->numeroProcessoAdministrativo);
        $this->appendElement($e, 'cMotivo', $data->codigoMotivo);
        $this->appendElement($e, 'xMotivo', $data->descricaoMotivo);
        $parent->appendChild($e);
    }

    private function buildE105105(DOMElement $parent, AnaliseFiscalData $data): void
    {
        $e = $this->dom->createElement('e105105');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'CPFAgTrib', $data->cpfAgenteTributario);
        $this->appendElement($e, 'nProcAdm', $data->numeroProcessoAdministrativo);
        $this->appendElement($e, 'cMotivo', $data->codigoMotivo);
        $this->appendElement($e, 'xMotivo', $data->descricaoMotivo);
        $parent->appendChild($e);
    }

    private function buildE202201(DOMElement $parent, ConfirmacaoPrestadorData $data): void
    {
        $e = $this->dom->createElement('e202201');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $parent->appendChild($e);
    }

    private function buildE203202(DOMElement $parent, ConfirmacaoTomadorData $data): void
    {
        $e = $this->dom->createElement('e203202');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $parent->appendChild($e);
    }

    private function buildE204203(DOMElement $parent, ConfirmacaoIntermediarioData $data): void
    {
        $e = $this->dom->createElement('e204203');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $parent->appendChild($e);
    }

    private function buildE205204(DOMElement $parent, ConfirmacaoTacitaData $data): void
    {
        $e = $this->dom->createElement('e205204');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $parent->appendChild($e);
    }

    private function buildE202205(DOMElement $parent, RejeicaoPrestadorData $data): void
    {
        $e = $this->dom->createElement('e202205');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendInfRej($e, $data->codigoMotivo, $data->motivo);
        $parent->appendChild($e);
    }

    private function buildE203206(DOMElement $parent, RejeicaoTomadorData $data): void
    {
        $e = $this->dom->createElement('e203206');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendInfRej($e, $data->codigoMotivo, $data->motivo);
        $parent->appendChild($e);
    }

    private function buildE204207(DOMElement $parent, RejeicaoIntermediarioData $data): void
    {
        $e = $this->dom->createElement('e204207');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendInfRej($e, $data->codigoMotivo, $data->motivo);
        $parent->appendChild($e);
    }

    private function buildE205208(DOMElement $parent, AnulacaoRejeicaoData $data): void
    {
        $e = $this->dom->createElement('e205208');
        $this->appendElement($e, 'xDesc', $data->descricao);

        $infAnRej = $this->dom->createElement('infAnRej');
        $this->appendElement($infAnRej, 'CPFAgTrib', $data->cpfAgenteTributario);
        $this->appendElement($infAnRej, 'idEvManifRej', $data->idEventoManifestacaoRejeicao);
        $this->appendElement($infAnRej, 'xMotivo', $data->motivo);
        $e->appendChild($infAnRej);

        $parent->appendChild($e);
    }

    private function buildE305101(DOMElement $parent, CancelamentoPorOficioData $data): void
    {
        $e = $this->dom->createElement('e305101');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'CPFAgTrib', $data->cpfAgenteTributario);
        $this->appendElement($e, 'nProcAdm', $data->numeroProcessoAdministrativo);
        $this->appendElement($e, 'xProcAdm', $data->descricaoProcessoAdministrativo);
        $parent->appendChild($e);
    }

    private function buildE305102(DOMElement $parent, BloqueioPorOficioData $data): void
    {
        $e = $this->dom->createElement('e305102');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'CPFAgTrib', $data->cpfAgenteTributario);
        $this->appendElement($e, 'xMotivo', $data->motivo);
        $this->appendElement($e, 'codEvento', $data->codigoEvento);
        $parent->appendChild($e);
    }

    private function buildE305103(DOMElement $parent, DesbloqueioPorOficioData $data): void
    {
        $e = $this->dom->createElement('e305103');
        $this->appendElement($e, 'xDesc', $data->descricao);
        $this->appendElement($e, 'CPFAgTrib', $data->cpfAgenteTributario);
        $this->appendElement($e, 'idBloqOfic', $data->idBloqueioOficio);
        $parent->appendChild($e);
    }

    private function appendInfRej(DOMElement $parent, ?string $codigoMotivo, ?string $motivo): void
    {
        $infRej = $this->dom->createElement('infRej');
        $this->appendElement($infRej, 'cMotivo', $codigoMotivo);
        $this->appendElement($infRej, 'xMotivo', $motivo);
        $parent->appendChild($infRej);
    }

    private function appendElement(DOMElement $parent, string $name, mixed $value): void
    {
        if ($value instanceof \BackedEnum) {
            $value = $value->value;
        }

        if ($value === null || $value === '') {
            return;
        }

        $element = $this->dom->createElement($name);
        $element->appendChild($this->dom->createTextNode((string) $value));
        $parent->appendChild($element);
    }
}
