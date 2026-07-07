<?php

declare(strict_types=1);

namespace Nfse\Danfse;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;
use InvalidArgumentException;

final class DanfseLayoutData
{
    /**
     * @return array<string, mixed>
     */
    public static function fromXml(string $xml, bool $cancelled = false, bool $substituted = false): array
    {
        $xml = self::sanitizeXml($xml);

        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);

        try {
            if (!$document->loadXML($xml)) {
                throw new InvalidArgumentException('XML da NFS-e inválido.');
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        $xpath = new DOMXPath($document);
        $infNfse = self::firstElement($xpath, $document, 'infNFSe');
        $dps = self::firstElement($xpath, $document, 'DPS');
        $prest = self::firstElement($xpath, $dps, 'prest');
        $emit = self::firstElement($xpath, $infNfse, 'emit');
        $enderNac = self::firstElement($xpath, $emit, 'enderNac');
        $prestAddress = self::firstElement($xpath, $prest, 'end') ?: $enderNac;
        $regTrib = self::firstElement($xpath, $prest, 'regTrib');
        $serv = self::firstElement($xpath, $dps, 'serv');
        $valores = self::firstElement($xpath, $infNfse, 'valores');
        $toma = self::firstElement($xpath, $dps, 'toma');
        $intermed = self::firstElement($xpath, $dps, 'interm');
        $recipient = self::firstElement($xpath, $dps, 'dest');
        $tribFed = self::firstElement($xpath, $dps, 'tribFed');
        $piscofins = self::firstElement($xpath, $tribFed, 'piscofins');
        $dpsIbsCbs = self::firstElement($xpath, $dps, 'IBSCBS');
        $nfseIbsCbs = self::firstElement($xpath, $infNfse, 'IBSCBS');
        $ibsCbs = $nfseIbsCbs ?: $dpsIbsCbs;
        $gIbsCbs = self::firstElement($xpath, $dpsIbsCbs, 'gIBSCBS');

        if (!$infNfse || !$dps || !$emit || !$serv || !$valores) {
            throw new InvalidArgumentException('XML da NFS-e não contém os grupos obrigatórios do DANFSe.');
        }

        [$compet] = self::formatDateTime(self::text($xpath, $dps, 'dCompet'));
        [$nfseDate, $nfseTime] = self::formatDateTime(self::text($xpath, $infNfse, 'dhProc'));
        [$dpsDate, $dpsTime] = self::formatDateTime(self::text($xpath, $dps, 'dhEmi'));

        $opSimpNac = self::text($xpath, $regTrib, 'opSimpNac');
        $regApTrib = self::text($xpath, $regTrib, 'regApTribSN');
        $regEspTrib = self::text($xpath, $regTrib, 'regEspTrib');
        $issqnType = self::text($xpath, $dps, 'tpRetISSQN');
        $issqnTypeForCalculation = $issqnType ?: '1';
        $issqnValue = self::text($xpath, $valores, 'vISSQN');
        $issqnRetained = in_array($issqnTypeForCalculation, ['2', '3'], true) ? self::number($issqnValue) : 0.0;
        $totalRetentions = self::text($xpath, $valores, 'vTotalRet');
        $totalFederalRetentions = $totalRetentions !== '' ? max(0.0, self::number($totalRetentions) - $issqnRetained) : 0.0;
        $description = self::text($xpath, $serv, 'xDescServ');
        $nationalTax = self::text($xpath, $serv, 'cTribNac');
        $key = self::nfseKey($infNfse);
        $vRetCp = self::text($xpath, $tribFed, 'vRetCP');
        $vRetCsll = self::text($xpath, $tribFed, 'vRetCSLL');
        $pis = self::text($xpath, $piscofins, 'vPis');
        $cofins = self::text($xpath, $piscofins, 'vCofins');
        $pisCofinsDebit = self::number($pis) + self::number($cofins);
        $issuerCity = self::text($xpath, $prest, 'xMun') ?: self::text($xpath, $prest, 'xCidade') ?: self::text($xpath, $infNfse, 'xLocEmi');
        $issuerUf = self::text($xpath, $prest, 'UF') ?: self::text($xpath, $emit, 'UF');
        $issuerCityState = self::cityState($issuerCity, $issuerUf);
        $substitutedKey = self::text($xpath, $dps, 'chSubstda');
        $cStat = self::text($xpath, $infNfse, 'cStat');
        $substituted = $substituted || $substitutedKey !== '' || $cStat === '101';
        $tribIssqnRaw = self::text($xpath, $dps, 'tribISSQN');
        $issqnNotSubject = in_array($tribIssqnRaw, ['3', '4'], true);
        $recipientIsTaker = self::recipientIsTaker($xpath, $dpsIbsCbs, $toma, $recipient);
        $competYear = (int) substr(self::text($xpath, $dps, 'dCompet'), 0, 4);
        $federalTaxBlockPrinted = $competYear === 0 || $competYear <= 2026;
        $totalIbsCbs = self::sumCurrencyValues(
            self::text($xpath, $ibsCbs, 'vIBSTot'),
            self::text($xpath, $ibsCbs, 'vCBS'),
        );

        $data = [
            'cancelled' => $cancelled,
            'substituted' => $substituted,
            'environment' => self::text($xpath, $dps, 'tpAmb'),
            'environment_notice' => self::environmentNotice(self::text($xpath, $dps, 'tpAmb')),
            'municipality_environment' => str_starts_with($nationalTax, '99') ? '' : 'Município: ' . $issuerCityState,
            'environment_generator' => self::text($xpath, $infNfse, 'ambGer') ?: '-',
            'environment_type' => self::environmentType(self::text($xpath, $dps, 'tpAmb')),
            'key_nfse' => $key,
            'qr_code_url' => 'https://www.nfse.gov.br/ConsultaPublica/?tpc=1&chave=' . $key,
            'nfse_number' => self::text($xpath, $infNfse, 'nNFSe'),
            'compet' => $compet,
            'dt_nfse' => $nfseDate,
            'hr_nfse' => $nfseTime,
            'dt_dps' => $dpsDate,
            'hr_dps' => $dpsTime,
            'dps_number' => self::text($xpath, $dps, 'nDPS'),
            'dps_serie' => self::text($xpath, $dps, 'serie'),
            'issuer_type' => self::issuerType(self::text($xpath, $dps, 'tpEmit')),
            'nfse_status' => self::nfseStatus(self::text($xpath, $infNfse, 'cStat'), $cancelled, $substituted),
            'purpose' => self::purpose(self::text($xpath, $dps, 'finNFSe')),
            'watermark' => self::watermark($cancelled, $substituted),
            'recipient_is_taker' => $recipientIsTaker,
            'issqn_not_subject' => $issqnNotSubject,
            'issuer' => [
                'id' => self::formatCpfCnpj(self::text($xpath, $prest, 'CNPJ') ?: self::text($xpath, $prest, 'CPF') ?: self::text($xpath, $emit, 'CNPJ') ?: self::text($xpath, $emit, 'CPF')),
                'municipal_registration' => self::text($xpath, $prest, 'IM') ?: self::text($xpath, $emit, 'IM') ?: '-',
                'phone' => self::formatPhone(self::text($xpath, $prest, 'fone') ?: self::text($xpath, $emit, 'fone')) ?: '-',
                'name' => self::text($xpath, $prest, 'xNome') ?: self::text($xpath, $emit, 'xNome') ?: self::text($xpath, $emit, 'xFant') ?: '-',
                'email' => self::text($xpath, $prest, 'email') ?: self::text($xpath, $emit, 'email') ?: '-',
                'address' => self::formatAddress($xpath, $prestAddress),
                'city' => trim($issuerCity . ' - ' . $issuerUf, ' -') ?: '-',
                'city_state' => $issuerCityState,
                'city_code_cep' => self::codeCep(self::text($xpath, $prestAddress, 'cMun'), self::text($xpath, $prestAddress, 'CEP')),
                'cep' => self::formatCep(self::text($xpath, $prestAddress, 'CEP')),
                'simples' => self::simplesNacional($opSimpNac),
                'tax_regim' => $opSimpNac === '3' ? self::taxRegime($regApTrib) : '-',
            ],
            'taker' => self::personData($xpath, $toma),
            'recipient' => self::personData($xpath, $recipient),
            'intermed' => self::personData($xpath, $intermed),
            'service' => [
                'tax_code' => self::serviceTaxCode($nationalTax, self::text($xpath, $serv, 'cTribMun')),
                'national_tax_code' => trim(self::formatNationalTaxCode($nationalTax) . ' - ' . self::descriptionWithoutCodePrefix($description), ' -') ?: '-',
                'municipal_tax_code' => self::text($xpath, $serv, 'cTribMun') ?: '-',
                'nbs_code' => self::text($xpath, $serv, 'cNBS') ?: '-',
                'place_of_provision' => self::text($xpath, $serv, 'cLocPrestacao') ?: '-',
                'country' => self::text($xpath, $serv, 'cPaisPrestacao') ?: '-',
                'place' => self::servicePlace(self::text($xpath, $infNfse, 'xLocPrestacao'), $issuerUf, self::text($xpath, $serv, 'cPaisPrestacao')),
                'tax_code_description' => self::text($xpath, $infNfse, 'xTribMun') ?: self::text($xpath, $infNfse, 'xTribNac') ?: '-',
                'description' => $description ?: '-',
            ],
            'municipal_taxes' => [
                'issqn_tax' => self::issqnTax($tribIssqnRaw),
                'country' => self::text($xpath, $dps, 'cPaisResult') ?: '-',
                'city' => self::municipalIncidence($xpath, $infNfse, $dps, $issuerUf),
                'special_tax_regim' => self::specialTaxRegime($regEspTrib),
                'immunity_type' => self::text($xpath, $dps, 'tpImunidade') ?: '-',
                'suspension_issqn' => 'Não',
                'suspension_number' => '-',
                'municipal_benefit' => self::municipalBenefit(self::text($xpath, $valores, 'tpBM')),
                'service_amount' => self::optionalCurrency(self::text($xpath, $dps, 'vServ')),
                'discount_unconditioned' => '-',
                'deduct_reduc_amount' => '-',
                'municipal_benefit_math' => '-',
                'calculation_basis' => self::optionalCurrency(self::text($xpath, $valores, 'vBC')),
                'aliq_applied' => self::percentage(self::text($xpath, $valores, 'pAliqAplic')),
                'issqn_retention' => self::issqnRetention($issqnType),
                'issqn_cleared' => $issqnTypeForCalculation === '1' ? self::optionalCurrency($issqnValue) : self::currency('0'),
            ],
            'federal_taxes' => [
                'printed' => $federalTaxBlockPrinted,
                'irrf' => self::optionalCurrency(self::text($xpath, $tribFed, 'vRetIRRF')),
                'previdenciary_contribution' => self::optionalCurrency($vRetCp),
                'social_contribution' => self::optionalCurrency($vRetCsll),
                'social_description' => self::pisCofinsRetention(self::text($xpath, $piscofins, 'tpRetPisCofins')),
                'pis_debit' => self::optionalCurrency($pis),
                'cofins_debit' => self::optionalCurrency($cofins),
            ],
            'ibs_cbs' => [
                'cst_classification' => self::slashJoin(
                    self::text($xpath, $gIbsCbs, 'CST') ?: self::text($xpath, $ibsCbs, 'CST'),
                    self::text($xpath, $gIbsCbs, 'cClassTrib') ?: self::text($xpath, $ibsCbs, 'cClassTrib'),
                ),
                'operation_indicator' => self::slashJoin(
                    self::text($xpath, $dpsIbsCbs, 'cIndOp') ?: self::text($xpath, $ibsCbs, 'cIndOp'),
                    self::text($xpath, $ibsCbs, 'cLocalidadeIncid'),
                    self::text($xpath, $ibsCbs, 'xLocalidadeIncid'),
                    self::text($xpath, $ibsCbs, 'UF'),
                ),
                'exclusions_reductions' => self::currencyOrDash(self::sumRawValues(
                    self::text($xpath, $dps, 'vDescIncond'),
                    self::text($xpath, $ibsCbs, 'vCalcReeRepRes'),
                    self::text($xpath, $valores, 'vISSQN'),
                    $pis,
                    $cofins,
                )),
                'base_after_reductions' => self::optionalCurrency(self::text($xpath, $ibsCbs, 'vBC')),
                'rate_reductions' => self::slashJoin(
                    self::percentage(self::text($xpath, $ibsCbs, 'pRedAliqUF')),
                    self::percentage(self::text($xpath, $ibsCbs, 'pRedAliqMun')),
                    self::percentage(self::text($xpath, $ibsCbs, 'pRedAliqCBS')),
                ),
                'ibs_rates' => self::slashJoin(
                    self::percentage(self::text($xpath, $ibsCbs, 'pIBSUF')),
                    self::percentage(self::text($xpath, $ibsCbs, 'pIBSMun')),
                ),
                'ibs_municipal_effective_rate' => self::percentage(self::text($xpath, $ibsCbs, 'pAliqEfetMun')),
                'ibs_municipal_amount' => self::optionalCurrency(self::text($xpath, $ibsCbs, 'vIBSMun')),
                'ibs_state_effective_rate' => self::percentage(self::text($xpath, $ibsCbs, 'pAliqEfetUF')),
                'ibs_state_amount' => self::optionalCurrency(self::text($xpath, $ibsCbs, 'vIBSUF')),
                'ibs_total' => self::optionalCurrency(self::text($xpath, $ibsCbs, 'vIBSTot')),
                'cbs_rate' => self::percentage(self::text($xpath, $ibsCbs, 'pCBS')),
                'cbs_effective_rate' => self::percentage(self::text($xpath, $ibsCbs, 'pAliqEfetCBS')),
                'cbs_total' => self::optionalCurrency(self::text($xpath, $ibsCbs, 'vCBS')),
            ],
            'total_value' => [
                'service_amount' => self::optionalCurrency(self::text($xpath, $dps, 'vServ')),
                'discount_conditioned' => '-',
                'discount_unconditioned' => '-',
                'total_retentions' => self::optionalCurrency(self::text($xpath, $valores, 'vTotalRet')),
                'issqn_retained' => self::currency((string) $issqnRetained),
                'total_federal_retentions' => self::currency((string) $totalFederalRetentions),
                'pis_cofins_debit' => $pisCofinsDebit > 0 ? self::currency((string) $pisCofinsDebit) : '-',
                'net_value' => self::optionalCurrency(self::text($xpath, $valores, 'vLiq')),
                'total_ibs_cbs' => $totalIbsCbs,
                'net_value_with_ibs_cbs' => self::optionalCurrency(self::text($xpath, $ibsCbs, 'vTotNF')),
            ],
            'taxes_amount' => self::approximateTaxTotals($xpath, $dps),
            'canhoto_nfse_key' => trim((self::text($xpath, $infNfse, 'nNFSe') ?: '-') . ' / ' . $key),
        ];

        $data['complementary_info'] = self::complementaryInfo($xpath, $dps, $serv, $data['taxes_amount']);

        self::applyOptionalMunicipalGroups($data, $xpath, $dps);

        return $data;
    }

    private static function firstElement(DOMXPath $xpath, ?DOMNode $context, string $tag): ?DOMElement
    {
        if (!$context) {
            return null;
        }

        $nodes = $xpath->query('.//*[local-name() = "' . $tag . '"]', $context);
        $node = $nodes?->item(0);

        return $node instanceof DOMElement ? $node : null;
    }

    private static function text(DOMXPath $xpath, ?DOMNode $context, string $tag): string
    {
        $element = self::firstElement($xpath, $context, $tag);

        return trim((string) $element?->textContent);
    }

    private static function nfseKey(DOMElement $infNfse): string
    {
        $id = $infNfse->getAttribute('Id');

        return str_starts_with($id, 'NFS') ? substr($id, 3) : $id;
    }

    /**
     * @return array{0: string, 1: string}
     */
    private static function formatDateTime(string $value): array
    {
        if ($value === '') {
            return ['', ''];
        }

        $date = substr($value, 0, 10);
        $parts = explode('-', $date);

        if (count($parts) !== 3) {
            return [$date, substr($value, 11, 8)];
        }

        return [$parts[2] . '/' . $parts[1] . '/' . $parts[0], substr($value, 11, 8)];
    }

    private static function formatAddress(DOMXPath $xpath, ?DOMNode $node): string
    {
        if (!$node) {
            return '-';
        }

        $parts = array_filter([
            self::text($xpath, $node, 'xLgr'),
            self::text($xpath, $node, 'nro'),
            self::text($xpath, $node, 'xCpl'),
            self::text($xpath, $node, 'xBairro'),
        ], static fn (string $part): bool => $part !== '');

        return $parts === [] ? '-' : implode(', ', $parts);
    }

    /**
     * @return array<string, string>
     */
    private static function personData(DOMXPath $xpath, ?DOMNode $node): array
    {
        if (!$node) {
            return [
                'id' => '-',
                'municipal_registration' => '-',
                'phone' => '-',
                'name' => '-',
                'email' => '-',
                'address' => '-',
                'city' => '-',
                'city_state' => '-',
                'city_code_cep' => '-',
                'cep' => '-',
            ];
        }

        $city = trim(self::text($xpath, $node, 'xMun') . ' - ' . self::text($xpath, $node, 'UF'), ' -');

        return [
            'id' => self::formatCpfCnpj(self::text($xpath, $node, 'CNPJ') ?: self::text($xpath, $node, 'CPF')) ?: '-',
            'municipal_registration' => self::text($xpath, $node, 'IM') ?: '-',
            'phone' => self::formatPhone(self::text($xpath, $node, 'fone')) ?: '-',
            'name' => self::text($xpath, $node, 'xNome') ?: '-',
            'email' => self::text($xpath, $node, 'email') ?: '-',
            'address' => self::formatAddress($xpath, $node),
            'city' => $city ?: '-',
            'city_state' => self::cityState(self::text($xpath, $node, 'xMun'), self::text($xpath, $node, 'UF')),
            'city_code_cep' => self::codeCep(self::text($xpath, $node, 'cMun'), self::text($xpath, $node, 'CEP')),
            'cep' => self::formatCep(self::text($xpath, $node, 'CEP')),
        ];
    }

    private static function cityState(string $city, string $uf): string
    {
        return self::slashJoin($city, $uf);
    }

    private static function codeCep(string $cityCode, string $cep): string
    {
        return self::slashJoin($cityCode, self::formatCep($cep));
    }

    private static function serviceTaxCode(string $nationalTax, string $municipalTax): string
    {
        return self::slashJoin(self::formatNationalTaxCode($nationalTax), $municipalTax);
    }

    private static function servicePlace(string $city, string $uf, string $country): string
    {
        return self::slashJoin($city, $uf, $country);
    }

    private static function municipalIncidence(DOMXPath $xpath, DOMElement $infNfse, DOMElement $dps, string $issuerUf): string
    {
        return self::slashJoin(
            self::text($xpath, $infNfse, 'xLocIncid') ?: self::text($xpath, $infNfse, 'cLocIncid'),
            $issuerUf,
            self::text($xpath, $dps, 'cPaisResult'),
        );
    }

    private static function environmentNotice(string $environment): string
    {
        return $environment === '2' ? 'NFS-e SEM VALIDADE JURÍDICA' : '';
    }

    private static function environmentType(string $environment): string
    {
        return match ($environment) {
            '1' => 'Produção',
            '2' => 'Produção restrita',
            default => '-',
        };
    }

    private static function issuerType(string $value): string
    {
        return match ($value) {
            '1' => 'Prestador',
            '2' => 'Tomador',
            '3' => 'Intermediário',
            default => '-',
        };
    }

    private static function nfseStatus(string $value, bool $cancelled, bool $substituted): string
    {
        if ($cancelled) {
            return 'Cancelada';
        }

        if ($substituted) {
            return 'Substituída';
        }

        return match ($value) {
            '100' => 'NFS-e Gerada',
            '101' => 'NFS-e de Substituição Gerada',
            '102' => 'NFS-e de Decisão Judicial',
            '103' => 'NFS-e Avulsa',
            '107' => 'NFS-e MEI',
            default => $value === '' ? '-' : $value,
        };
    }

    private static function purpose(string $value): string
    {
        return match ($value) {
            '0' => 'NFS-e regular',
            default => $value === '' ? '-' : $value,
        };
    }

    private static function formatCpfCnpj(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if ($digits === '') {
            return '';
        }

        if (strlen($digits) > 11) {
            $digits = str_pad(substr($digits, -14), 14, '0', STR_PAD_LEFT);

            return substr($digits, 0, 2) . '.' . substr($digits, 2, 3) . '.' . substr($digits, 5, 3) . '/' . substr($digits, 8, 4) . '-' . substr($digits, 12, 2);
        }

        $digits = str_pad(substr($digits, -11), 11, '0', STR_PAD_LEFT);

        return substr($digits, 0, 3) . '.' . substr($digits, 3, 3) . '.' . substr($digits, 6, 3) . '-' . substr($digits, 9, 2);
    }

    private static function formatCep(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        if ($digits === '') {
            return '-';
        }

        $digits = str_pad(substr($digits, 0, 8), 8, '0', STR_PAD_RIGHT);

        return substr($digits, 0, 5) . '-' . substr($digits, 5, 3);
    }

    private static function formatPhone(string $value): string
    {
        $digits = preg_replace('/\D/', '', $value) ?? '';

        return match (strlen($digits)) {
            11 => '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 5) . '-' . substr($digits, 7),
            10 => '(' . substr($digits, 0, 2) . ') ' . substr($digits, 2, 4) . '-' . substr($digits, 6),
            default => $value,
        };
    }

    private static function descriptionWithoutCodePrefix(string $description): string
    {
        if (str_contains($description, ' - ')) {
            return trim((string) preg_replace('/^.*? - /', '', $description));
        }

        return trim($description);
    }

    private static function formatNationalTaxCode(string $value): string
    {
        if (strlen($value) >= 6) {
            return substr($value, 0, 2) . '.' . substr($value, 2, 2) . '.' . substr($value, 4);
        }

        return $value;
    }

    private static function number(string $value): float
    {
        if ($value === '') {
            return 0.0;
        }

        return (float) str_replace(',', '.', $value);
    }

    private static function currency(string $value): string
    {
        return 'R$ ' . number_format(self::number($value), 4, ',', '.');
    }

    private static function currencyOrDash(?float $value): string
    {
        return $value === null ? '-' : self::currency((string) $value);
    }

    private static function optionalCurrency(string $value): string
    {
        return $value === '' ? '-' : self::currency($value);
    }

    private static function percentage(string $value): string
    {
        return $value === '' ? '-' : number_format(self::number($value), 4, ',', '.') . '%';
    }

    private static function slashJoin(string ...$values): string
    {
        $parts = array_values(array_filter(
            array_map(static fn (string $value): string => trim($value), $values),
            static fn (string $value): bool => $value !== '' && $value !== '-',
        ));

        return $parts === [] ? '-' : implode(' / ', $parts);
    }

    private static function sumRawValues(string ...$values): ?float
    {
        $present = array_filter($values, static fn (string $value): bool => trim($value) !== '');

        if ($present === []) {
            return null;
        }

        return array_reduce(
            $present,
            static fn (float $carry, string $value): float => $carry + self::number($value),
            0.0,
        );
    }

    private static function sumCurrencyValues(string ...$values): string
    {
        return self::currencyOrDash(self::sumRawValues(...$values));
    }

    private static function simplesNacional(string $value): string
    {
        return match ($value) {
            '' => '-',
            '1' => 'Não Optante',
            '2' => 'Optante - Microempreendedor Individual (MEI)',
            '3' => 'Optante - Microempresa ou Empresa de Pequeno Porte (ME/EPP)',
            default => 'Não Optante',
        };
    }

    private static function taxRegime(string $value): string
    {
        return match ($value) {
            '1' => 'Regime de apuração dos tributos federais e municipal pelo Simples Nacional',
            '2' => 'Regime de apuração dos tributos federais pelo SN e o ISSQN pela NFS-e conforme respectiva legislação municipal do tributo',
            '3' => 'Regime de apuração dos tributos federais e municipal pela NFS-e conforme respectivas legislações federal e municipal de cada tributo',
            default => '-',
        };
    }

    private static function specialTaxRegime(string $value): string
    {
        return match ($value) {
            '' => '-',
            '0' => 'Nenhum',
            '1' => 'Ato Cooperado (Cooperativa)',
            '2' => 'Estimativa',
            '3' => 'Microempresa Municipal',
            '4' => 'Notário ou Registrador',
            '5' => 'Profissional Autônomo',
            '6' => 'Sociedade de Profissionais',
            '9' => 'Outros',
            default => 'Nenhum',
        };
    }

    private static function issqnTax(string $value): string
    {
        return match ($value) {
            '' => '-',
            '1' => 'Operação Tributável',
            '2' => 'Imunidade',
            '3' => 'Exportação de serviço',
            '4' => 'Não Incidência',
            default => 'Operação Tributável',
        };
    }

    private static function municipalBenefit(string $value): string
    {
        return match ($value) {
            '' => '-',
            '1' => 'Isenção',
            '2' => 'Redução da BC em %',
            '3' => 'Redução da BC em R$',
            '4' => 'Alíquota Diferenciada',
            default => $value,
        };
    }

    private static function issqnRetention(string $value): string
    {
        return match ($value) {
            '' => '-',
            '2' => 'Retido pelo Tomador',
            '3' => 'Retido pelo Intermediário',
            default => 'Não Retido',
        };
    }

    private static function pisCofinsRetention(string $value): string
    {
        return match ($value) {
            '' => '-',
            '1' => 'Retido',
            '2' => 'Não Retido',
            default => $value,
        };
    }

    private static function watermark(bool $cancelled, bool $substituted): ?string
    {
        if ($cancelled) {
            return 'CANCELADA';
        }

        if ($substituted) {
            return 'SUBSTITUÍDA';
        }

        return null;
    }

    /**
     * @param  array<string, string>  $taxesAmount
     */
    private static function complementaryInfo(DOMXPath $xpath, DOMElement $dps, DOMElement $serv, array $taxesAmount): string
    {
        $infoCompl = self::firstElement($xpath, $serv, 'infoCompl');
        $parts = [
            'Inf. Cont.: ' . (self::text($xpath, $infoCompl, 'xInfComp') ?: '-'),
        ];

        foreach ([
            'NFS-e Subst.:' => self::text($xpath, $dps, 'chSubstda'),
            'Doc. Ref.:' => self::text($xpath, $dps, 'docRef'),
            'Cod. Obra:' => self::text($xpath, $dps, 'cObra'),
            'Insc. Imob.:' => self::text($xpath, $dps, 'inscImobFisc'),
            'Cod. Evt.:' => self::text($xpath, $dps, 'idAtvEvt'),
            'Doc. Tec.:' => self::text($xpath, $dps, 'idDocTec'),
            'Núm. Ped.:' => self::text($xpath, $dps, 'xPed'),
            'Item Ped.:' => self::text($xpath, $dps, 'xItemPed'),
            'Inf. A. T. Mun.:' => self::text($xpath, $infoCompl, 'xOutInf'),
        ] as $label => $value) {
            if ($value !== '') {
                $parts[] = $label . ' ' . $value;
            }
        }

        $parts[] = sprintf(
            'Totais Aproximados dos Tributos cfe. Lei nº 12.741/2012: Federais: %s; Estaduais: %s; Municipais: %s',
            $taxesAmount['federal_tax'] ?? '-',
            $taxesAmount['state_tax'] ?? '-',
            $taxesAmount['municipal_tax'] ?? '-',
        );

        $text = implode(' | ', $parts);

        return strlen($text) <= 2000 ? $text : substr($text, 0, 1997) . '...';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function applyOptionalMunicipalGroups(array &$data, DOMXPath $xpath, DOMNode $dps): void
    {
        $exigSusp = self::firstElement($xpath, $dps, 'exigSusp');
        if ($exigSusp) {
            $type = self::text($xpath, $exigSusp, 'tpSusp');
            $data['municipal_taxes']['suspension_issqn'] = match ($type) {
                '1' => 'Exigibilidade do ISSQN Suspensa por Decisão Judicial',
                '2' => 'Exigibilidade do ISSQN Suspensa por Processo Administrativo',
                default => 'Sim',
            };
            $data['municipal_taxes']['suspension_number'] = self::text($xpath, $exigSusp, 'nProcesso') ?: '-';
        }

        $municipalBenefit = self::firstElement($xpath, $dps, 'BM');
        if ($municipalBenefit) {
            $data['municipal_taxes']['municipal_benefit'] = self::text($xpath, $municipalBenefit, 'nBM') ?: '-';
        }

        if (self::firstElement($xpath, $dps, 'vDescCondIncond')) {
            $unconditioned = self::currency(self::text($xpath, $dps, 'vDescIncond'));
            $data['municipal_taxes']['discount_unconditioned'] = $unconditioned;
            $data['total_value']['discount_conditioned'] = self::currency(self::text($xpath, $dps, 'vDescCond'));
            $data['total_value']['discount_unconditioned'] = $unconditioned;
        }

        if (self::firstElement($xpath, $dps, 'vDedRed')) {
            $data['municipal_taxes']['deduct_reduc_amount'] = self::currency(self::text($xpath, $dps, 'vDR'));
        }
    }

    private static function recipientIsTaker(DOMXPath $xpath, ?DOMElement $dpsIbsCbs, ?DOMElement $toma, ?DOMElement $recipient): bool
    {
        $indDest = self::text($xpath, $dpsIbsCbs, 'indDest');
        if ($indDest === '0') {
            return true;
        }

        if (! $toma || ! $recipient) {
            return false;
        }

        $takerId = preg_replace('/\D/', '', self::text($xpath, $toma, 'CNPJ') ?: self::text($xpath, $toma, 'CPF')) ?? '';
        $recipientId = preg_replace('/\D/', '', self::text($xpath, $recipient, 'CNPJ') ?: self::text($xpath, $recipient, 'CPF')) ?? '';

        return $takerId !== '' && $takerId === $recipientId;
    }

    /**
     * @return array{federal_tax: string, state_tax: string, municipal_tax: string}
     */
    private static function approximateTaxTotals(DOMXPath $xpath, DOMElement $dps): array
    {
        $totTrib = self::firstElement($xpath, $dps, 'totTrib');
        $vTotTrib = self::firstElement($xpath, $totTrib, 'vTotTrib');

        return [
            'federal_tax' => self::taxTotalDisplay(
                self::text($xpath, $vTotTrib, 'vTotTribFed'),
                self::text($xpath, $totTrib, 'pTotTribFed'),
            ),
            'state_tax' => self::taxTotalDisplay(
                self::text($xpath, $vTotTrib, 'vTotTribEst'),
                self::text($xpath, $totTrib, 'pTotTribEst'),
            ),
            'municipal_tax' => self::taxTotalDisplay(
                self::text($xpath, $vTotTrib, 'vTotTribMun'),
                self::text($xpath, $totTrib, 'pTotTribMun') ?: self::text($xpath, $totTrib, 'pTotTribSN'),
            ),
        ];
    }

    private static function taxTotalDisplay(string $amount, string $percent): string
    {
        if ($amount !== '') {
            return self::optionalCurrency($amount);
        }

        if ($percent !== '') {
            return self::percentage($percent);
        }

        return '-';
    }

    private static function sanitizeXml(string $xml): string
    {
        if (! mb_check_encoding($xml, 'UTF-8')) {
            $xml = mb_convert_encoding($xml, 'UTF-8', 'ISO-8859-1');
        }

        $xml = iconv('UTF-8', 'UTF-8//IGNORE', $xml);
        $xml = str_replace('\"', '"', $xml);

        return preg_replace('/&(?!amp;|lt;|gt;|quot;|apos;|#\d+;|#x[\da-fA-F]+;)/', '&amp;', $xml) ?? $xml;
    }
}