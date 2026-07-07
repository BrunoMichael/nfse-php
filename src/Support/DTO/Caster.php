<?php

declare(strict_types=1);

namespace Nfse\Support\DTO;

interface Caster
{
    public function cast(mixed $value): mixed;
}
