<?php

declare(strict_types=1);

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\ThemeStudio\InertiaBookingsVue\Health\InertiaBookingsVueHealthCheck;
use Capell\ThemeStudio\InertiaBookingsVue\Providers\InertiaBookingsVueServiceProvider;
use Capell\ThemeStudio\InertiaBookingsVue\Tests\InertiaBookingsVueTestCase;

require_once __DIR__ . '/../../src/Health/InertiaBookingsVueHealthCheck.php';
require_once __DIR__ . '/../../src/Providers/InertiaBookingsVueServiceProvider.php';
require_once __DIR__ . '/../InertiaBookingsVueTestCase.php';

uses(InertiaBookingsVueTestCase::class);

it('registers the vue theme component source as frontend assets', function (): void {
    $sources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(static fn (VendorAssetData $asset): bool => $asset->packageName === InertiaBookingsVueServiceProvider::$packageName)
        ->map(static fn (VendorAssetData $asset): string => $asset->path())
        ->values()
        ->all();

    expect($sources)->toBe(['resources/js/**/*.vue']);
});

it('ships the shared inertia component contract for the bookings theme', function (): void {
    $basePath = dirname(__DIR__, 2);

    expect(file_exists($basePath . '/resources/js/Pages/Capell/Page.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Bookings/Request.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Content.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Image.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Title.vue'))->toBeTrue()
        ->and((new InertiaBookingsVueHealthCheck)->passes())->toBeTrue();
});
