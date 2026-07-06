<?php

namespace Nfse\Service;

use Nfse\Contract\DanfseRendererInterface;
use Nfse\Danfse\DanfseMapper;
use Nfse\Danfse\DanfseOutputFormat;
use Nfse\Danfse\DanfseWatermark;
use Nfse\Danfse\Renderer\HtmlDanfseRenderer;
use Nfse\Danfse\Renderer\PdfDanfseRenderer;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Http\NfseContext;
use Nfse\Xml\NfseXmlParser;

class DanfseService
{
    private DanfseMapper $mapper;

    private NfseXmlParser $parser;

    public function __construct(
        private NfseContext $context,
        ?DanfseMapper $mapper = null,
        ?NfseXmlParser $parser = null,
    ) {
        $this->mapper = $mapper ?? new DanfseMapper;
        $this->parser = $parser ?? new NfseXmlParser;
    }

    public function html(NfseData $nfse, ?DanfseWatermark $watermark = null): string
    {
        return $this->gerar($nfse, DanfseOutputFormat::Html, $watermark);
    }

    public function pdf(NfseData $nfse, ?DanfseWatermark $watermark = null): string
    {
        return $this->gerar($nfse, DanfseOutputFormat::Pdf, $watermark);
    }

    public function gerar(
        NfseData $nfse,
        DanfseOutputFormat $format = DanfseOutputFormat::Html,
        ?DanfseWatermark $watermark = null,
    ): string {
        $viewModel = $this->mapper->map($nfse, $watermark);

        return $this->rendererFor($format)->render($viewModel);
    }

    public function gerarDeXml(
        string $xml,
        DanfseOutputFormat $format = DanfseOutputFormat::Html,
        ?DanfseWatermark $watermark = null,
    ): string {
        return $this->gerar($this->parser->parse($xml), $format, $watermark);
    }

    private function rendererFor(DanfseOutputFormat $format): DanfseRendererInterface
    {
        return match ($format) {
            DanfseOutputFormat::Html => new HtmlDanfseRenderer,
            DanfseOutputFormat::Pdf => new PdfDanfseRenderer,
        };
    }
}
