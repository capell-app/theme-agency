<?php

declare(strict_types=1);

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Assets\VendorAssetConditionRegistry;
use Capell\Inertia\Support\InertiaAdapterRegistry;
use Capell\InertiaReactAdapter\Health\InertiaReactAdapterHealthCheck;
use Capell\InertiaReactAdapter\Providers\InertiaReactAdapterServiceProvider;
use Capell\InertiaReactAdapter\Tests\InertiaReactAdapterTestCase;

require_once __DIR__ . '/../../../inertia/src/Data/InertiaAdapterData.php';
require_once __DIR__ . '/../../../inertia/src/Support/InertiaAdapterRegistry.php';
require_once __DIR__ . '/../../../inertia/src/Providers/InertiaServiceProvider.php';
require_once __DIR__ . '/../../src/Health/InertiaReactAdapterHealthCheck.php';
require_once __DIR__ . '/../../src/Providers/InertiaReactAdapterServiceProvider.php';
require_once __DIR__ . '/../InertiaReactAdapterTestCase.php';

uses(InertiaReactAdapterTestCase::class);

it('registers the react adapter with the inertia bridge', function (): void {
    $adapter = resolve(InertiaAdapterRegistry::class)->get(InertiaReactAdapterServiceProvider::ADAPTER_KEY);

    throw_if($adapter === null, RuntimeException::class, 'React adapter was not registered.');

    expect($adapter)->not->toBeNull()
        ->and($adapter->key)->toBe('react')
        ->and($adapter->packageName)->toBe(InertiaReactAdapterServiceProvider::$packageName)
        ->and($adapter->buildPath)->toBe(InertiaReactAdapterServiceProvider::BUILD_PATH)
        ->and($adapter->entrypoint)->toBe(InertiaReactAdapterServiceProvider::ENTRYPOINT)
        ->and($adapter->npmDependencies)->toMatchArray([
            '@inertiajs/react' => '^3.0.0',
            '@vitejs/plugin-react' => '^5.0.0',
            'react' => '^19.0.0',
            'react-dom' => '^19.0.0',
        ])
        ->and($adapter->components)->toMatchArray([
            'bookingRequest' => 'Capell/Bookings/Request',
            'page' => 'Capell/Page',
        ]);
});

it('registers npm dependencies and the react build entrypoint as vendor assets', function (): void {
    $npmDependencies = CapellCore::getVendorAssetsForType(VendorAssetEnum::NpmDependency)
        ->filter(static fn (VendorAssetData $asset): bool => $asset->packageName === InertiaReactAdapterServiceProvider::$packageName)
        ->mapWithKeys(static fn (VendorAssetData $asset): array => [$asset->dependencyName() => $asset->dependencyVersion()])
        ->all();

    $buildAssets = CapellCore::getVendorAssetsForType(VendorAssetEnum::BuildAsset)
        ->filter(static fn (VendorAssetData $asset): bool => $asset->packageName === InertiaReactAdapterServiceProvider::$packageName)
        ->map(static fn (VendorAssetData $asset): array => [
            'path' => $asset->path(),
            'file' => $asset->file(),
        ])
        ->values()
        ->all();

    expect($npmDependencies)->toBe([
        '@inertiajs/react' => '^3.0.0',
        '@vitejs/plugin-react' => '^5.0.0',
        'react' => '^19.0.0',
        'react-dom' => '^19.0.0',
    ])->and($buildAssets)->toContain([
        'path' => InertiaReactAdapterServiceProvider::BUILD_PATH,
        'file' => InertiaReactAdapterServiceProvider::ENTRYPOINT,
    ]);
});

it('disables the generic react build condition when the bookings react component pack is installed', function (): void {
    config()->set('capell-inertia.adapter', InertiaReactAdapterServiceProvider::ADAPTER_KEY);

    $context = (object) [
        'runtime' => (object) [
            'usesInertia' => true,
        ],
    ];

    $registry = resolve(VendorAssetConditionRegistry::class);

    expect($registry->passes('capell-inertia-adapter-react', $context))->toBeTrue();

    CapellCore::forcePackageInstalled(InertiaReactAdapterServiceProvider::THEME_BOOKINGS_REACT_PACKAGE);

    expect($registry->passes('capell-inertia-adapter-react', $context))->toBeFalse();
});

it('passes health when the react adapter has been registered', function (): void {
    expect(InertiaReactAdapterHealthCheck::compatibleCapellApiVersion())->toBe('^4.0')
        ->and((new InertiaReactAdapterHealthCheck)->passes())->toBeTrue();
});
