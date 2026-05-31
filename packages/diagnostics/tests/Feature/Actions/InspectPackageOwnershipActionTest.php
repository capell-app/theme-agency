<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\InspectPackageOwnershipAction;
use Capell\Diagnostics\Data\PackageOwnershipCandidateData;
use Capell\Diagnostics\Tests\Fixtures\PackageOwnershipProbeController;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

it('identifies package ownership for config files and database tables', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_package_ownership_' . uniqid();
    $packagesPath = $temporaryRoot . '/packages';
    $packagePath = $packagesPath . '/catalog-demo';

    File::ensureDirectoryExists($packagePath . '/config');
    File::ensureDirectoryExists($packagePath . '/database/migrations');

    File::put($packagePath . '/config/capell-catalog-demo.php', '<?php return [];');
    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/catalog-demo',
        'slug' => 'catalog-demo',
        'displayName' => 'Catalog Demo',
        'namespace' => 'Capell\\CatalogDemo',
        'database' => [
            'requiredTables' => ['demo_records'],
        ],
    ], JSON_THROW_ON_ERROR));
    File::put($packagePath . '/database/migrations/2026_05_31_000001_create_demo_records_table.php', <<<'PHP'
<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class {
    public function up(): void
    {
        Schema::create('demo_records', function (Blueprint $table): void {
            $table->id();
        });
    }
};
PHP);

    try {
        $action = new InspectPackageOwnershipAction(customPackagesPath: $packagesPath);

        $configResult = $action->handle('config', 'capell-catalog-demo.php');
        $configCandidate = $configResult->candidates->toCollection()->first();
        $tableResult = $action->handle('table', 'demo_records');
        $tableCandidates = $tableResult->candidates->toCollection();

        throw_unless($configCandidate instanceof PackageOwnershipCandidateData, RuntimeException::class, 'Expected config ownership candidate to be present.');

        expect($configResult->foundCount)->toBe(1)
            ->and($configCandidate->composerName)->toBe('capell-app/catalog-demo')
            ->and($configCandidate->source)->toBe('config')
            ->and($configCandidate->evidence)->toBe('config/capell-catalog-demo.php')
            ->and($tableResult->foundCount)->toBe(2)
            ->and($tableCandidates->pluck('source')->all())->toBe(['manifest', 'migration'])
            ->and($tableCandidates->pluck('evidence')->all())->toBe([
                'capell.json database.requiredTables',
                'database/migrations/2026_05_31_000001_create_demo_records_table.php',
            ]);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

it('identifies package ownership for runtime routes by controller namespace', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_package_ownership_' . uniqid();
    $packagesPath = $temporaryRoot . '/packages';
    $packagePath = $packagesPath . '/diagnostics-fixture';

    File::ensureDirectoryExists($packagePath);
    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/diagnostics-fixture',
        'slug' => 'diagnostics-fixture',
        'displayName' => 'Diagnostics Fixture',
        'namespace' => 'Capell\\Diagnostics\\Tests\\Fixtures',
    ], JSON_THROW_ON_ERROR));

    Route::get('/diagnostics-owner-probe', PackageOwnershipProbeController::class)
        ->name('diagnostics.owner.probe');

    try {
        $result = (new InspectPackageOwnershipAction(customPackagesPath: $packagesPath))->handle('route', 'diagnostics.owner.probe');
        $candidate = $result->candidates->toCollection()->first();

        throw_unless($candidate instanceof PackageOwnershipCandidateData, RuntimeException::class, 'Expected route ownership candidate to be present.');

        expect($result->foundCount)->toBe(1)
            ->and($candidate->composerName)->toBe('capell-app/diagnostics-fixture')
            ->and($candidate->source)->toBe('route')
            ->and($candidate->evidence)->toContain('name: diagnostics.owner.probe')
            ->and($candidate->evidence)->toContain('uri: diagnostics-owner-probe')
            ->and($candidate->evidence)->toContain(PackageOwnershipProbeController::class);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});
