<?php

namespace Nfse\Danfse\Renderer;

use Nfse\Contract\DanfseRendererInterface;
use Nfse\Danfse\DanfseViewModel;

final class HtmlDanfseRenderer implements DanfseRendererInterface
{
    public function render(DanfseViewModel $viewModel): string
    {
        ob_start();
        include __DIR__.'/templates/danfse.php';

        return (string) ob_get_clean();
    }
}
