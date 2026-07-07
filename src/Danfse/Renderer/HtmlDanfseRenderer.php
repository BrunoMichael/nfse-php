<?php

namespace Nfse\Danfse\Renderer;

use Nfse\Contract\DanfseRendererInterface;

final class HtmlDanfseRenderer implements DanfseRendererInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function render(array $data, string $qrCodeDataUri): string
    {
        ob_start();
        include __DIR__.'/templates/danfse.php';

        return (string) ob_get_clean();
    }
}
