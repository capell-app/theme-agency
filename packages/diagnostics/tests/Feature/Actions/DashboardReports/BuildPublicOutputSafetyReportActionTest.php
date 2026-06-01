<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\DashboardReports\BuildPublicOutputSafetyReportAction;
use Illuminate\Support\Facades\File;

it('reports public output safety from frontend package manifests', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_public_output_safety_' . uniqid();
    $packagesPath = $temporaryRoot . '/packages';

    File::ensureDirectoryExists($packagesPath . '/safe-package');
    File::ensureDirectoryExists($packagesPath . '/sensitive-package');
    File::ensureDirectoryExists($packagesPath . '/cacheable-package');
    File::ensureDirectoryExists($packagesPath . '/admin-package');

    File::put($packagesPath . '/safe-package/capell.json', diagnosticsPublicOutputManifest('capell-app/safe-package', [
        'cacheable' => true,
        'sensitiveOutput' => false,
        'variesBy' => ['site', 'locale'],
    ]));
    File::put($packagesPath . '/sensitive-package/capell.json', diagnosticsPublicOutputManifest('capell-app/sensitive-package', [
        'cacheable' => false,
        'sensitiveOutput' => true,
        'variesBy' => [],
    ]));
    File::put($packagesPath . '/cacheable-package/capell.json', diagnosticsPublicOutputManifest('capell-app/cacheable-package', [
        'cacheable' => true,
        'sensitiveOutput' => false,
        'variesBy' => [],
    ]));
    File::put($packagesPath . '/admin-package/capell.json', json_encode([
        'name' => 'capell-app/admin-package',
        'surfaces' => ['admin'],
    ], JSON_THROW_ON_ERROR));

    try {
        $reports = collect(BuildPublicOutputSafetyReportAction::run($packagesPath))->keyBy('key');

        expect($reports->get('public-output.safe-package')?->status)->toBe('ok')
            ->and($reports->get('public-output.sensitive-package')?->status)->toBe('error')
            ->and($reports->get('public-output.cacheable-package')?->status)->toBe('warning')
            ->and($reports)->not->toHaveKey('public-output.admin-package');
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

it('warns when no local packages path can be scanned', function (): void {
    $reports = BuildPublicOutputSafetyReportAction::run(sys_get_temp_dir() . '/missing_public_output_safety_' . uniqid());

    expect($reports[0]->key)->toBe('public-output.packages-path')
        ->and($reports[0]->status)->toBe('warning');
});

/**
 * @param  array<string, mixed>  $cacheSafety
 */
function diagnosticsPublicOutputManifest(string $name, array $cacheSafety): string
{
    return json_encode([
        'name' => $name,
        'surfaces' => ['frontend'],
        'performance' => [
            'cacheSafety' => $cacheSafety,
        ],
    ], JSON_THROW_ON_ERROR);
}
