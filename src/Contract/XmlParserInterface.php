<?php

namespace Nfse\Contract;

use Nfse\Enums\DocumentType;
use Nfse\Enums\SchemaVersion;

interface XmlParserInterface
{
    public function parse(string $xml): object;

    public function supportedDocument(): DocumentType;

    public function supportedVersion(): SchemaVersion;
}
