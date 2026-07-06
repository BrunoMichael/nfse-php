<?php

namespace Nfse\Danfse;

final class DanfseFormatter
{
    public static function dash(?string $value): string
    {
        $value = trim((string) $value);

        return $value === '' ? '-' : $value;
    }

    public static function money(?float $value): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format($value, 2, ',', '.');
    }

    public static function percent(?float $value): string
    {
        if ($value === null) {
            return '-';
        }

        return number_format($value, 2, ',', '.').'%';
    }

    public static function date(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('d/m/Y', $timestamp) : $value;
    }

    public static function dateTime(?string $value): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $timestamp = strtotime($value);

        return $timestamp ? date('d/m/Y H:i:s', $timestamp) : $value;
    }

    public static function truncate(?string $value, int $limit): string
    {
        $value = (string) $value;
        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return mb_substr($value, 0, $limit - 3).'...';
    }

    public static function chaveFromId(?string $id): string
    {
        if ($id === null) {
            return '';
        }

        return preg_replace('/^NFS/i', '', $id) ?? '';
    }

    public static function documento(?string $cpf, ?string $cnpj, ?string $nif = null): string
    {
        if ($cnpj) {
            return \Nfse\Support\CpfCnpjFormatter::formatCnpj($cnpj);
        }

        if ($cpf) {
            return \Nfse\Support\CpfCnpjFormatter::formatCpf($cpf);
        }

        return self::dash($nif);
    }

    public static function enderecoNacional(?\Nfse\Dto\Nfse\EnderecoData $endereco): string
    {
        if ($endereco === null) {
            return '-';
        }

        $partes = array_filter([
            $endereco->logradouro,
            $endereco->numero,
            $endereco->complemento,
            $endereco->bairro,
        ], fn ($v) => $v !== null && $v !== '' && $v !== '-');

        return self::truncate(implode(', ', $partes), 80);
    }

    public static function enderecoEmitente(?\Nfse\Dto\Nfse\EnderecoEmitenteData $endereco): string
    {
        if ($endereco === null) {
            return '-';
        }

        $partes = array_filter([
            $endereco->logradouro,
            $endereco->numero,
            $endereco->complemento,
            $endereco->bairro,
        ], fn ($v) => $v !== null && $v !== '' && $v !== '-');

        return self::truncate(implode(', ', $partes), 80);
    }

    public static function formatCep(?string $cep): string
    {
        if ($cep === null || $cep === '') {
            return '-';
        }

        return \Nfse\Support\CpfCnpjFormatter::formatCep($cep);
    }
}
