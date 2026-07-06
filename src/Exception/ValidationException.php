<?php

namespace Nfse\Exception;

use InvalidArgumentException;

class ValidationException extends InvalidArgumentException
{
    /**
     * @param  string[]  $errors
     */
    public function __construct(public readonly array $errors)
    {
        parent::__construct('Validação da DPS falhou: '.implode('; ', $errors));
    }
}
