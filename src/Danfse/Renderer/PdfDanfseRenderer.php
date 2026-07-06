<?php

namespace Nfse\Danfse\Renderer;

use Nfse\Contract\DanfseRendererInterface;
use Nfse\Danfse\DanfseViewModel;

final class PdfDanfseRenderer implements DanfseRendererInterface
{
    public function __construct(
        private ?DanfseRendererInterface $htmlRenderer = null,
    ) {
        $this->htmlRenderer ??= new HtmlDanfseRenderer;
    }

    public function render(DanfseViewModel $viewModel): string
    {
        if (! class_exists(\Dompdf\Dompdf::class)) {
            throw new \RuntimeException(
                'Instale dompdf/dompdf para gerar DANFSe em PDF: composer require dompdf/dompdf'
            );
        }

        $html = $this->htmlRenderer->render($viewModel);

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
