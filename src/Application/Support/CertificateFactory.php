<?php

namespace Nfse\Application\Support;

use Nfse\Http\NfseContext;
use Nfse\Signer\Certificate;

final class CertificateFactory
{
    public static function fromContext(NfseContext $context): Certificate
    {
        if ($context->certificateContent !== null) {
            return Certificate::fromContent($context->certificateContent, $context->certificatePassword);
        }

        return new Certificate($context->certificatePath, $context->certificatePassword);
    }
}
