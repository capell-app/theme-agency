<?php

declare(strict_types=1);

use Capell\Diagnostics\Actions\Dashboard\BuildPackagesInstalledAction;
use Capell\Diagnostics\Tests\Fixtures\Health\PassingFixtureHealthCheck;
use Capell\Diagnostics\Tests\Fixtures\Health\StubFixtureHealthCheck;
use Illuminate\Support\Facades\File;

it('hydrates installed package health metadata from local package manifests', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_packages_installed_' . uniqid();
    $installedJsonPath = $temporaryRoot . '/vendor/composer/installed.json';
    $packagesPath = $temporaryRoot . '/packages';
    $packagePath = $packagesPath . '/events';

    File::ensureDirectoryExists(dirname($installedJsonPath));
    File::ensureDirectoryExists($packagePath . '/config');

    File::put($installedJsonPath, json_encode([
        'packages' => [
            ['name' => 'capell-app/events', 'version' => '4.x-dev'],
            ['name' => 'vendor/ignored', 'version' => '1.0.0'],
        ],
    ], JSON_THROW_ON_ERROR));

    File::put($packagePath . '/config/capell-events.php', '<?php return [];');
    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/events',
        'slug' => 'events',
        'displayName' => 'Events',
        'product' => ['bundle' => 'growth'],
        'commands' => [
            'install' => 'capell:events-install',
            'doctor' => 'capell:events-doctor',
        ],
        'healthChecks' => [
            ['key' => 'events.tables', 'label' => 'Tables', 'class' => PassingFixtureHealthCheck::class],
            ['key' => 'events.routes', 'label' => 'Routes', 'class' => StubFixtureHealthCheck::class],
            ['key' => 'events.missing', 'label' => 'Missing', 'class' => 'Missing\\EventsHealthCheck'],
            ['key' => 'events.invalid', 'label' => 'Invalid', 'class' => stdClass::class],
        ],
    ], JSON_THROW_ON_ERROR));

    try {
        $result = (new BuildPackagesInstalledAction($installedJsonPath, $packagesPath))->handle();
        $package = diagnosticsPackageInfo($result->packages->toCollection()->first());

        expect($result->packages)->toHaveCount(1)
            ->and($package->name)->toBe('events')
            ->and($package->displayName)->toBe('Events')
            ->and($package->bundle)->toBe('growth')
            ->and($package->healthCheckCount)->toBe(4)
            ->and($package->healthCheckDeclaredCount)->toBe(4)
            ->and($package->healthCheckImplementedCount)->toBe(1)
            ->and($package->healthCheckStubCount)->toBe(1)
            ->and($package->healthCheckBrokenCount)->toBe(2)
            ->and($package->installCommand)->toBe('capell:events-install')
            ->and($package->doctorCommand)->toBe('capell:events-doctor');
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

it('still lists unknown capell packages when no local manifest is available', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_packages_installed_' . uniqid();
    $installedJsonPath = $temporaryRoot . '/vendor/composer/installed.json';
    $packagesPath = $temporaryRoot . '/packages';

    File::ensureDirectoryExists(dirname($installedJsonPath));
    File::ensureDirectoryExists($packagesPath);
    File::put($installedJsonPath, json_encode([
        'packages' => [
            ['name' => 'capell-app/custom-package', 'version' => 'dev-main'],
        ],
    ], JSON_THROW_ON_ERROR));

    try {
        $result = (new BuildPackagesInstalledAction($installedJsonPath, $packagesPath))->handle();
        $package = diagnosticsPackageInfo($result->packages->toCollection()->first());

        expect($result->packages)->toHaveCount(1)
            ->and($package->name)->toBe('custom-package')
            ->and($package->docsUrl)->toBeNull()
            ->and($package->healthCheckCount)->toBe(0);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

it('hydrates installed package metadata from composer install paths', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_packages_installed_' . uniqid();
    $installedJsonPath = $temporaryRoot . '/vendor/composer/installed.json';
    $packagePath = $temporaryRoot . '/vendor/capell-app/events';
    $packagesPath = $temporaryRoot . '/missing-packages';

    File::ensureDirectoryExists(dirname($installedJsonPath));
    File::ensureDirectoryExists($packagePath . '/config');

    File::put($installedJsonPath, json_encode([
        'packages' => [
            [
                'name' => 'capell-app/events',
                'version' => '4.x-dev',
                'install_path' => '../capell-app/events',
            ],
        ],
    ], JSON_THROW_ON_ERROR));

    File::put($packagePath . '/config/capell-events.php', '<?php return [];');
    File::put($packagePath . '/capell.json', json_encode([
        'name' => 'capell-app/events',
        'slug' => 'events',
        'displayName' => 'Events',
        'product' => ['bundle' => 'growth'],
        'commands' => [
            'install' => 'capell:events-install',
            'doctor' => 'capell:events-doctor',
        ],
        'healthChecks' => [
            ['key' => 'events.tables', 'label' => 'Tables', 'class' => PassingFixtureHealthCheck::class],
            ['key' => 'events.routes', 'label' => 'Routes', 'class' => StubFixtureHealthCheck::class],
        ],
    ], JSON_THROW_ON_ERROR));

    try {
        $result = (new BuildPackagesInstalledAction($installedJsonPath, $packagesPath))->handle();
        $package = diagnosticsPackageInfo($result->packages->toCollection()->first());

        expect($result->packages)->toHaveCount(1)
            ->and($package->name)->toBe('events')
            ->and($package->displayName)->toBe('Events')
            ->and($package->bundle)->toBe('growth')
            ->and($package->healthCheckCount)->toBe(2)
            ->and($package->healthCheckDeclaredCount)->toBe(2)
            ->and($package->healthCheckImplementedCount)->toBe(1)
            ->and($package->healthCheckStubCount)->toBe(1)
            ->and($package->healthCheckBrokenCount)->toBe(0)
            ->and($package->installCommand)->toBe('capell:events-install')
            ->and($package->doctorCommand)->toBe('capell:events-doctor');
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});

it('reads health check counts from real package manifests', function (): void {
    $temporaryRoot = sys_get_temp_dir() . '/capell_packages_installed_' . uniqid();
    $installedJsonPath = $temporaryRoot . '/vendor/composer/installed.json';

    File::ensureDirectoryExists(dirname($installedJsonPath));
    File::put($installedJsonPath, json_encode([
        'packages' => [
            ['name' => 'capell-app/api', 'version' => '4.x-dev'],
            ['name' => 'capell-app/diagnostics', 'version' => '4.x-dev'],
            ['name' => 'capell-app/migration-assistant', 'version' => '4.x-dev'],
        ],
    ], JSON_THROW_ON_ERROR));

    try {
        $packagesPath = dirname(__DIR__, 5);
        $result = (new BuildPackagesInstalledAction($installedJsonPath, $packagesPath))->handle();
        $packages = $result->packages->toCollection()->keyBy('composerName');
        $apiPackage = diagnosticsPackageInfo($packages->get('capell-app/api'));
        $diagnosticsPackage = diagnosticsPackageInfo($packages->get('capell-app/diagnostics'));
        $migrationAssistantPackage = diagnosticsPackageInfo($packages->get('capell-app/migration-assistant'));

        expect($packages)->toHaveKeys([
            'capell-app/api',
            'capell-app/diagnostics',
            'capell-app/migration-assistant',
        ])
            ->and($apiPackage->healthCheckCount)->toBeGreaterThan(0)
            ->and($diagnosticsPackage->healthCheckCount)->toBeGreaterThan(0)
            ->and($diagnosticsPackage->healthCheckImplementedCount)->toBeGreaterThan(0)
            ->and($migrationAssistantPackage->healthCheckCount)->toBeGreaterThan(0);
    } finally {
        File::deleteDirectory($temporaryRoot);
    }
});
