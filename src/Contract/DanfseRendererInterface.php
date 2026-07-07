<?php

namespace Nfse\Contract;

interface DanfseRendererInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function render(array $data, string $qrCodeDataUri): string;
}
