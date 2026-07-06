<?php

namespace Nfse\Danfse\Support;

use Nfse\Contract\QrCodeGeneratorInterface;

final class CompositeQrCodeGenerator implements QrCodeGeneratorInterface
{
    public function __construct(
        private ?QrCodeGeneratorInterface $primary = null,
        private ?QrCodeGeneratorInterface $fallback = null,
    ) {
        $this->primary ??= new ChillerlanQrCodeGenerator;
        $this->fallback ??= new SvgPlaceholderQrCodeGenerator;
    }

    public function generateDataUri(string $content, int $size = 150): string
    {
        if (class_exists(\chillerlan\QRCode\QRCode::class)) {
            return $this->primary->generateDataUri($content, $size);
        }

        return $this->fallback->generateDataUri($content, $size);
    }
}
