<?php

namespace Nfse\Xml;

use DOMDocument;
use DOMElement;
use Nfse\Dto\Nfse\IbscbsNfseData;
use Nfse\Dto\Nfse\InfoIbscbsData;
use Nfse\Dto\Nfse\InfoTributacaoIbscbsData;
use Nfse\Dto\Nfse\InfoValoresIbscbsData;
use Nfse\Dto\Nfse\SituacaoClassificacaoIbscbsData;
use Nfse\Dto\Nfse\TotCbsData;
use Nfse\Dto\Nfse\TotCibsData;
use Nfse\Dto\Nfse\TotIbsData;
use Nfse\Dto\Nfse\TotIbsMunData;
use Nfse\Dto\Nfse\TotIbsUfData;
use Nfse\Dto\Nfse\ValoresIbscbsFedData;
use Nfse\Dto\Nfse\ValoresIbscbsMunData;
use Nfse\Dto\Nfse\ValoresIbscbsNfseData;
use Nfse\Dto\Nfse\ValoresIbscbsUfData;

final class IbscbsXmlBuilder
{
    public function __construct(private DOMDocument $dom) {}

    public function buildInfoIbscbs(DOMElement $parent, InfoIbscbsData $data): void
    {
        $ibscbs = $this->dom->createElement('IBSCBS');
        $this->appendElement($ibscbs, 'finNFSe', $data->finalidade);
        $this->appendElement($ibscbs, 'indFinal', $data->indicadorConsumoFinal);
        $this->appendElement($ibscbs, 'cIndOp', $data->codigoIndicadorOperacao);
        $this->appendElement($ibscbs, 'tpOper', $data->tipoOperacao);
        $this->appendElement($ibscbs, 'indDest', $data->indicadorDestinatario);

        if ($data->valores) {
            $this->buildInfoValores($ibscbs, $data->valores);
        }

        $parent->appendChild($ibscbs);
    }

    public function buildNfseIbscbs(DOMElement $parent, IbscbsNfseData $data): void
    {
        $ibscbs = $this->dom->createElement('IBSCBS');
        $this->appendElement($ibscbs, 'cLocalidadeIncid', $data->codigoLocalidadeIncidencia);
        $this->appendElement($ibscbs, 'xLocalidadeIncid', $data->nomeLocalidadeIncidencia);
        $this->appendElement($ibscbs, 'pRedutor', $data->percentualRedutor);

        if ($data->valores) {
            $this->buildNfseValores($ibscbs, $data->valores);
        }

        if ($data->totalizadores) {
            $this->buildTotCibs($ibscbs, $data->totalizadores);
        }

        $parent->appendChild($ibscbs);
    }

    private function buildInfoValores(DOMElement $parent, InfoValoresIbscbsData $data): void
    {
        if ($data->tributacao === null) {
            return;
        }

        $valores = $this->dom->createElement('valores');
        $this->buildInfoTributacao($valores, $data->tributacao);
        $parent->appendChild($valores);
    }

    private function buildInfoTributacao(DOMElement $parent, InfoTributacaoIbscbsData $data): void
    {
        if ($data->situacaoClassificacao === null) {
            return;
        }

        $trib = $this->dom->createElement('trib');
        $gIbscbs = $this->dom->createElement('gIBSCBS');
        $this->buildSituacaoClassificacao($gIbscbs, $data->situacaoClassificacao);
        $trib->appendChild($gIbscbs);
        $parent->appendChild($trib);
    }

    private function buildSituacaoClassificacao(DOMElement $parent, SituacaoClassificacaoIbscbsData $data): void
    {
        $this->appendElement($parent, 'CST', $data->codigoSituacaoTributaria);
        $this->appendElement($parent, 'cClassTrib', $data->codigoClassificacaoTributaria);
        $this->appendElement($parent, 'cCredPres', $data->codigoCreditoPresumido);
    }

    private function buildNfseValores(DOMElement $parent, ValoresIbscbsNfseData $data): void
    {
        $valores = $this->dom->createElement('valores');
        $this->appendElement($valores, 'vBC', $data->baseCalculo, true);
        $this->appendElement($valores, 'vCalcReeRepRes', $data->valorReeRepRes, true);

        if ($data->uf) {
            $this->buildValoresUf($valores, $data->uf);
        }

        if ($data->municipio) {
            $this->buildValoresMun($valores, $data->municipio);
        }

        if ($data->federal) {
            $this->buildValoresFed($valores, $data->federal);
        }

        $parent->appendChild($valores);
    }

    private function buildValoresUf(DOMElement $parent, ValoresIbscbsUfData $data): void
    {
        $uf = $this->dom->createElement('uf');
        $this->appendElement($uf, 'pIBSUF', $data->aliquotaIbsUf, true);
        $this->appendElement($uf, 'pRedAliqUF', $data->percentualReducaoAliquotaUf, true);
        $this->appendElement($uf, 'pAliqEfetUF', $data->aliquotaEfetivaUf, true);
        $parent->appendChild($uf);
    }

    private function buildValoresMun(DOMElement $parent, ValoresIbscbsMunData $data): void
    {
        $mun = $this->dom->createElement('mun');
        $this->appendElement($mun, 'pIBSMun', $data->aliquotaIbsMunicipal, true);
        $this->appendElement($mun, 'pRedAliqMun', $data->percentualReducaoAliquotaMunicipal, true);
        $this->appendElement($mun, 'pAliqEfetMun', $data->aliquotaEfetivaMunicipal, true);
        $parent->appendChild($mun);
    }

    private function buildValoresFed(DOMElement $parent, ValoresIbscbsFedData $data): void
    {
        $fed = $this->dom->createElement('fed');
        $this->appendElement($fed, 'pCBS', $data->aliquotaCbs, true);
        $this->appendElement($fed, 'pRedAliqCBS', $data->percentualReducaoAliquotaCbs, true);
        $this->appendElement($fed, 'pAliqEfetCBS', $data->aliquotaEfetivaCbs, true);
        $parent->appendChild($fed);
    }

    private function buildTotCibs(DOMElement $parent, TotCibsData $data): void
    {
        $tot = $this->dom->createElement('totCIBS');
        $this->appendElement($tot, 'vTotNF', $data->valorTotalNf, true);

        if ($data->totalIbs) {
            $this->buildTotIbs($tot, $data->totalIbs);
        }

        if ($data->totalCbs) {
            $this->buildTotCbs($tot, $data->totalCbs);
        }

        $parent->appendChild($tot);
    }

    private function buildTotIbs(DOMElement $parent, TotIbsData $data): void
    {
        $gIbs = $this->dom->createElement('gIBS');
        $this->appendElement($gIbs, 'vIBSTot', $data->valorTotalIbs, true);

        if ($data->totalUf) {
            $this->buildTotIbsUf($gIbs, $data->totalUf);
        }

        if ($data->totalMunicipal) {
            $this->buildTotIbsMun($gIbs, $data->totalMunicipal);
        }

        $parent->appendChild($gIbs);
    }

    private function buildTotIbsUf(DOMElement $parent, TotIbsUfData $data): void
    {
        $g = $this->dom->createElement('gIBSUFTot');
        $this->appendElement($g, 'vDifUF', $data->valorDiferimentoUf, true);
        $this->appendElement($g, 'vIBSUF', $data->valorIbsUf, true);
        $parent->appendChild($g);
    }

    private function buildTotIbsMun(DOMElement $parent, TotIbsMunData $data): void
    {
        $g = $this->dom->createElement('gIBSMunTot');
        $this->appendElement($g, 'vDifMun', $data->valorDiferimentoMunicipal, true);
        $this->appendElement($g, 'vIBSMun', $data->valorIbsMunicipal, true);
        $parent->appendChild($g);
    }

    private function buildTotCbs(DOMElement $parent, TotCbsData $data): void
    {
        $g = $this->dom->createElement('gCBS');
        $this->appendElement($g, 'vDifCBS', $data->valorDiferimentoCbs, true);
        $this->appendElement($g, 'vCBS', $data->valorCbs, true);
        $parent->appendChild($g);
    }

    private function appendElement(DOMElement $parent, string $name, mixed $value, bool $asDecimal = false): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (is_object($value) && enum_exists($value::class)) {
            $value = $value->value;
        }

        if ($asDecimal && is_numeric($value)) {
            $value = number_format((float) $value, 2, '.', '');
        }

        $parent->appendChild($this->dom->createElement($name, (string) $value));
    }
}
