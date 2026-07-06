<?php

namespace Nfse\Contract;

interface QrCodeGeneratorInterface
{
    public function generateDataUri(string $content, int $size = 150): string;
}
