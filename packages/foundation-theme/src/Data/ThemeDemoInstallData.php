<?php

declare(strict_types=1);

namespace Capell\FoundationTheme\Data;

use Spatie\LaravelData\Data;

final class ThemeDemoInstallData extends Data
{
    /** @var array<int, string> */
    public readonly array $siteNames;

    /** @var array<int, string> */
    public readonly array $languageCodes;

    public readonly string $baseUrl;

    public readonly bool $force;

    /**
     * @param  array<int, string>  $siteNames
     * @param  array<int, string>  $languageCodes
     */
    public function __construct(
        array $siteNames,
        array $languageCodes,
        string $baseUrl,
        bool $force = false,
    ) {
        $this->siteNames = self::normalizeStrings($siteNames);
        $this->languageCodes = array_map(
            static fn (string $languageCode): string => strtolower($languageCode),
            self::normalizeStrings($languageCodes),
        );
        $this->baseUrl = rtrim(trim($baseUrl), '/');
        $this->force = $force;
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private static function normalizeStrings(array $values): array
    {
        return array_values(array_filter(
            array_map(
                static fn (string $value): string => trim($value),
                $values,
            ),
            static fn (string $value): bool => $value !== '',
        ));
    }
}
