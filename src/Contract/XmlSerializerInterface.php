<?php

namespace Nfse\Contract;

use Nfse\Enums\DocumentType;
use Nfse\Enums\SchemaVersion;

interface XmlSerializerInterface
{
    public function serialize(object $document): string;

    public function supportedDocument(): DocumentType;

    public function supportedVersion(): SchemaVersion;
}
