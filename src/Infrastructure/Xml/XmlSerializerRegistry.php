<?php

namespace Nfse\Infrastructure\Xml;

use Nfse\Contract\XmlParserInterface;
use Nfse\Contract\XmlSerializerInterface;
use Nfse\Enums\DocumentType;
use Nfse\Enums\SchemaVersion;
use InvalidArgumentException;
use Nfse\Dto\Nfse\DpsData;
use Nfse\Dto\Nfse\NfseData;
use Nfse\Dto\Nfse\PedRegEventoData;
use Nfse\Xml\DpsXmlBuilder;
use Nfse\Xml\EventosXmlBuilder;
use Nfse\Xml\NfseXmlParser;

class XmlSerializerRegistry
{
    /** @var array<string, XmlSerializerInterface> */
    private array $serializers = [];

    public function register(XmlSerializerInterface $serializer): void
    {
        $key = $this->key($serializer->supportedDocument(), $serializer->supportedVersion());
        $this->serializers[$key] = $serializer;
    }

    public function serialize(DocumentType $document, SchemaVersion $version, object $payload): string
    {
        $serializer = $this->resolveSerializer($document, $version);

        return $serializer->serialize($payload);
    }

    public function parse(DocumentType $document, SchemaVersion $version, string $xml): object
    {
        if ($document !== DocumentType::Nfse) {
            throw new InvalidArgumentException("Parser não registrado para {$document->value} {$version->value}.");
        }

        return (new NfseXmlParser)->parse($xml);
    }

    public function detectVersion(string $xml): SchemaVersion
    {
        if (preg_match('/\bversao="([^"]+)"/', $xml, $matches)) {
            return SchemaVersion::fromString($matches[1]);
        }

        return SchemaVersion::V1_01;
    }

    private function resolveSerializer(DocumentType $document, SchemaVersion $version): XmlSerializerInterface
    {
        $key = $this->key($document, $version);

        if (isset($this->serializers[$key])) {
            return $this->serializers[$key];
        }

        return match ($document) {
            DocumentType::Dps => new DpsXmlSerializer($version),
            DocumentType::PedRegEvento => new PedRegEventoXmlSerializer($version),
            default => throw new InvalidArgumentException("Serializer não registrado para {$document->value} {$version->value}."),
        };
    }

    private function key(DocumentType $document, SchemaVersion $version): string
    {
        return $document->value.'@'.$version->value;
    }
}

final class DpsXmlSerializer implements XmlSerializerInterface
{
    public function __construct(private SchemaVersion $version = SchemaVersion::V1_01) {}

    public function serialize(object $document): string
    {
        if (! $document instanceof DpsData) {
            throw new InvalidArgumentException('Esperado DpsData.');
        }

        if ($document->versao === null) {
            $document->versao = $this->version->value;
        }

        return (new DpsXmlBuilder)->build($document);
    }

    public function supportedDocument(): DocumentType
    {
        return DocumentType::Dps;
    }

    public function supportedVersion(): SchemaVersion
    {
        return $this->version;
    }
}

final class PedRegEventoXmlSerializer implements XmlSerializerInterface
{
    public function __construct(private SchemaVersion $version = SchemaVersion::V1_01) {}

    public function serialize(object $document): string
    {
        if (! $document instanceof PedRegEventoData) {
            throw new InvalidArgumentException('Esperado PedRegEventoData.');
        }

        if ($document->versao === '') {
            $document->versao = $this->version->value;
        }

        return (new EventosXmlBuilder)->buildPedRegEvento($document);
    }

    public function supportedDocument(): DocumentType
    {
        return DocumentType::PedRegEvento;
    }

    public function supportedVersion(): SchemaVersion
    {
        return $this->version;
    }
}

final class NfseXmlParserAdapter implements XmlParserInterface
{
    public function __construct(private SchemaVersion $version = SchemaVersion::V1_01) {}

    public function parse(string $xml): object
    {
        $parsed = (new NfseXmlParser)->parse($xml);

        if (! $parsed instanceof NfseData) {
            throw new InvalidArgumentException('Esperado NfseData.');
        }

        return $parsed;
    }

    public function supportedDocument(): DocumentType
    {
        return DocumentType::Nfse;
    }

    public function supportedVersion(): SchemaVersion
    {
        return $this->version;
    }
}
