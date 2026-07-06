<?php

namespace Nfse\Contract;

interface DanfseRendererInterface
{
    public function render(\Nfse\Danfse\DanfseViewModel $viewModel): string;
}
