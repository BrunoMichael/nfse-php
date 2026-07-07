<?php

namespace Nfse\Service;

use Nfse\Contract\DanfseRendererInterface;
use Nfse\Contract\QrCodeGeneratorInterface;
use Nfse\Danfse\DanfseLayoutData;
use Nfse\Danfse\DanfseOutputFormat;
use Nfse\Danfse\DanfseWatermark;
use Nfse\Danfse\Renderer\HtmlDanfseRenderer;
use Nfse\Danfse\Renderer\PdfDanfseRenderer;
use Nfse\Danfse\Support\CompositeQrCodeGenerator;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Http\NfseContext;
use Nfse\Xml\NfseXmlBuilder;

class DanfseService
{
    private QrCodeGeneratorInterface $qrCodeGenerator;

    private NfseXmlBuilder $xmlBuilder;

    public function __construct(
        private NfseContext $context,
        ?QrCodeGeneratorInterface $qrCodeGenerator = null,
        ?NfseXmlBuilder $xmlBuilder = null,
    ) {
        $this->qrCodeGenerator = $qrCodeGenerator ?? new CompositeQrCodeGenerator;
        $this->xmlBuilder = $xmlBuilder ?? new NfseXmlBuilder;
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
        return $this->renderFromXml(
            $this->xmlBuilder->build($nfse),
            $format,
            $watermark,
        );
    }

    public function gerarDeXml(
        string $xml,
        DanfseOutputFormat $format = DanfseOutputFormat::Html,
        ?DanfseWatermark $watermark = null,
    ): string {
        return $this->renderFromXml($xml, $format, $watermark);
    }

    private function renderFromXml(
        string $xml,
        DanfseOutputFormat $format,
        ?DanfseWatermark $watermark,
    ): string {
        [$cancelled, $substituted] = $this->watermarkFlags($watermark);
        $data = DanfseLayoutData::fromXml($xml, $cancelled, $substituted);
        $qrCodeDataUri = $this->qrCodeGenerator->generateDataUri((string) $data['qr_code_url'], 150);

        return $this->rendererFor($format)->render($data, $qrCodeDataUri);
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function watermarkFlags(?DanfseWatermark $watermark): array
    {
        return match ($watermark) {
            DanfseWatermark::Cancelada => [true, false],
            DanfseWatermark::Substituída => [false, true],
            default => [false, false],
        };
    }

    private function rendererFor(DanfseOutputFormat $format): DanfseRendererInterface
    {
        return match ($format) {
            DanfseOutputFormat::Html => new HtmlDanfseRenderer,
            DanfseOutputFormat::Pdf => new PdfDanfseRenderer,
        };
    }
}
