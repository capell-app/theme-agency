<?php

declare(strict_types=1);

use Capell\FoundationTheme\Actions\ValidateThemeCatalogueEntryAction;
use Capell\FoundationTheme\Data\ThemeValidationResultData;

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($autoload)) {
    require_once $autoload;
}

/**
 * Standalone-script mirror of `capell:validate-themes`, following the same
 * plain-`vendor/autoload.php`-only invocation pattern as
 * `scripts/audit-manifest-v3.php` — no Testbench/Laravel boot required,
 * since {@see ValidateThemeCatalogueEntryAction} only calls each theme's
 * static `definition()` method. Wired into the composer `manifest:check`
 * chain alongside the manifest-v3 audit.
 *
 * @return list<ThemeValidationResultData>
 */
function capell_validate_themes(string $root): array
{
    $packagesRoot = $root . DIRECTORY_SEPARATOR . 'packages';
    $manifestPaths = glob($packagesRoot . '/theme-*/capell.json') ?: [];
    sort($manifestPaths);

    $results = [];

    foreach ($manifestPaths as $manifestPath) {
        $decoded = json_decode((string) file_get_contents($manifestPath), true);

        if (! is_array($decoded) || ($decoded['kind'] ?? null) !== 'theme') {
            continue;
        }

        $packageDirectory = basename(dirname($manifestPath));

        $results[] = ValidateThemeCatalogueEntryAction::run($packageDirectory, $packagesRoot);
    }

    return $results;
}

if (realpath((string) ($_SERVER['SCRIPT_FILENAME'] ?? '')) === __FILE__) {
    $results = capell_validate_themes(dirname(__DIR__));

    $report = array_map(
        static fn (ThemeValidationResultData $result): array => [
            'themeKey' => $result->themeKey,
            'passes' => $result->passes(),
            'violations' => $result->violations,
        ],
        $results,
    );

    echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;

    $failures = array_filter($results, static fn (ThemeValidationResultData $result): bool => ! $result->passes());

    exit /* status */ ($results !== [] && $failures === [] ? 0 : 1);
}
