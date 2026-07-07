<?php

namespace Nfse\Danfse\Renderer;

use Nfse\Contract\DanfseRendererInterface;

final class PdfDanfseRenderer implements DanfseRendererInterface
{
    public function __construct(
        private ?DanfseRendererInterface $htmlRenderer = null,
    ) {
        $this->htmlRenderer ??= new HtmlDanfseRenderer;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function render(array $data, string $qrCodeDataUri): string
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            throw new \RuntimeException(
                'Instale dompdf/dompdf para gerar DANFSe em PDF: composer require dompdf/dompdf'
            );
        }

        $html = $this->htmlRenderer->render($data, $qrCodeDataUri);

        $dompdf = new \Dompdf\Dompdf([
            'isRemoteEnabled' => true,
            'isHtml5ParserEnabled' => true,
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }
}
