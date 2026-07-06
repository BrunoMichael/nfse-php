<?php

namespace Nfse\Danfse\Support;

use Nfse\Contract\QrCodeGeneratorInterface;

final class SvgPlaceholderQrCodeGenerator implements QrCodeGeneratorInterface
{
    public function generateDataUri(string $content, int $size = 150): string
    {
        $hash = md5($content);
        $cells = 21;
        $cellSize = (int) floor($size / $cells);
        $svgSize = $cellSize * $cells;
        $rects = '';

        for ($y = 0; $y < $cells; $y++) {
            for ($x = 0; $x < $cells; $x++) {
                $index = ($x + $y) % strlen($hash);
                if (hexdec($hash[$index]) % 2 === 0) {
                    continue;
                }

                $rects .= sprintf(
                    '<rect x="%d" y="%d" width="%d" height="%d" fill="#000"/>',
                    $x * $cellSize,
                    $y * $cellSize,
                    $cellSize,
                    $cellSize
                );
            }
        }

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$svgSize}" height="{$svgSize}" viewBox="0 0 {$svgSize} {$svgSize}">
{$rects}
</svg>
SVG;

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
