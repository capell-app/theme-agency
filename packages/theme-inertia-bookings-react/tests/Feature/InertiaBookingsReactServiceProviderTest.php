<?php

declare(strict_types=1);

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\ThemeStudio\InertiaBookingsReact\Health\InertiaBookingsReactHealthCheck;
use Capell\ThemeStudio\InertiaBookingsReact\Providers\InertiaBookingsReactServiceProvider;
use Capell\ThemeStudio\InertiaBookingsReact\Tests\InertiaBookingsReactTestCase;

require_once __DIR__ . '/../../src/Health/InertiaBookingsReactHealthCheck.php';
require_once __DIR__ . '/../../src/Providers/InertiaBookingsReactServiceProvider.php';
require_once __DIR__ . '/../InertiaBookingsReactTestCase.php';

uses(InertiaBookingsReactTestCase::class);

it('registers the react theme component source as frontend assets', function (): void {
    $sources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(static fn (VendorAssetData $asset): bool => $asset->packageName === InertiaBookingsReactServiceProvider::$packageName)
        ->map(static fn (VendorAssetData $asset): string => $asset->path())
        ->values()
        ->all();

    expect($sources)->toBe(['resources/js/**/*.jsx']);
});

it('ships the shared inertia component contract for the bookings theme', function (): void {
    $basePath = dirname(__DIR__, 2);

    expect(file_exists($basePath . '/resources/js/Pages/Capell/Page.jsx'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Bookings/Request.jsx'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Content.jsx'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Image.jsx'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Title.jsx'))->toBeTrue()
        ->and((new InertiaBookingsReactHealthCheck)->passes())->toBeTrue();
});
