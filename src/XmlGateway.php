<?php

namespace Nfse;

use Nfse\Enums\DocumentType;
use Nfse\Enums\SchemaVersion;
use Nfse\Http\NfseContext;
use Nfse\Infrastructure\Xml\XmlSerializerRegistry;

class XmlGateway
{
    private XmlSerializerRegistry $registry;

    public function __construct(
        private NfseContext $context,
        ?XmlSerializerRegistry $registry = null,
    ) {
        $this->registry = $registry ?? new XmlSerializerRegistry;
    }

    public function document(DocumentType $document): XmlDocumentBuilder
    {
        return new XmlDocumentBuilder($this->registry, $document);
    }
}

class XmlDocumentBuilder
{
    private SchemaVersion $version = SchemaVersion::V1_01;

    public function __construct(
        private XmlSerializerRegistry $registry,
        private DocumentType $document,
    ) {}

    public function version(SchemaVersion $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function serialize(object $payload): string
    {
        return $this->registry->serialize($this->document, $this->version, $payload);
    }

    public function parse(string $xml): object
    {
        $version = $this->registry->detectVersion($xml);

        return $this->registry->parse($this->document, $version, $xml);
    }
}
