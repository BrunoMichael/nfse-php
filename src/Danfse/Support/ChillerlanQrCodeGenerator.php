<?php

namespace Nfse\Danfse\Support;

use Nfse\Contract\QrCodeGeneratorInterface;

final class ChillerlanQrCodeGenerator implements QrCodeGeneratorInterface
{
    public function generateDataUri(string $content, int $size = 150): string
    {
        if (! class_exists(\chillerlan\QRCode\QRCode::class)) {
            throw new \RuntimeException(
                'Instale chillerlan/php-qrcode para gerar QR Code escaneável: composer require chillerlan/php-qrcode'
            );
        }

        $options = new \chillerlan\QRCode\QROptions([
            'outputType' => \chillerlan\QRCode\Output\QROutputInterface::GDIMAGE_PNG,
            'scale' => max(2, (int) round($size / 50)),
            'imageBase64' => true,
        ]);

        return (new \chillerlan\QRCode\QRCode($options))->render($content);
    }
}
