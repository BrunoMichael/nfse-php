<?php

declare(strict_types=1);

namespace Nfse\Danfse;

final class DanfseAssets
{
    public static function logoBase64(): ?string
    {
        $path = __DIR__.'/Renderer/assets/nfse-logo-horizontal.png';

        if (! is_file($path)) {
            return null;
        }

        return base64_encode((string) file_get_contents($path));
    }
}
