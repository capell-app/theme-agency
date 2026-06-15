<?php

declare(strict_types=1);

use Capell\Core\Data\VendorAssetData;
use Capell\Core\Enums\VendorAssetEnum;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Support\Assets\VendorAssetConditionRegistry;
use Capell\ThemeStudio\InertiaBookingsVue\Health\InertiaBookingsVueHealthCheck;
use Capell\ThemeStudio\InertiaBookingsVue\Providers\InertiaBookingsVueServiceProvider;
use Capell\ThemeStudio\InertiaBookingsVue\Tests\InertiaBookingsVueTestCase;

require_once __DIR__ . '/../../src/Health/InertiaBookingsVueHealthCheck.php';
require_once __DIR__ . '/../../src/Providers/InertiaBookingsVueServiceProvider.php';
require_once __DIR__ . '/../InertiaBookingsVueTestCase.php';

uses(InertiaBookingsVueTestCase::class);

it('registers the vue theme component source and build entrypoint as frontend assets', function (): void {
    $sources = CapellCore::getVendorAssetsForType(VendorAssetEnum::TailwindSource)
        ->filter(static fn (VendorAssetData $asset): bool => $asset->packageName === InertiaBookingsVueServiceProvider::$packageName)
        ->map(static fn (VendorAssetData $asset): string => $asset->path())
        ->values()
        ->all();

    $buildAssets = CapellCore::getVendorAssetsForType(VendorAssetEnum::BuildAsset)
        ->filter(static fn (VendorAssetData $asset): bool => $asset->packageName === InertiaBookingsVueServiceProvider::$packageName)
        ->map(static fn (VendorAssetData $asset): array => [
            'path' => $asset->path(),
            'file' => $asset->file(),
            'condition' => $asset->condition(),
        ])
        ->values()
        ->all();

    config()->set('capell-inertia.adapter', 'vue');

    $context = (object) [
        'runtime' => (object) [
            'usesInertia' => true,
        ],
    ];

    expect($sources)->toBe(['resources/js/**/*.vue'])
        ->and($buildAssets)->toContain([
            'path' => InertiaBookingsVueServiceProvider::BUILD_PATH,
            'file' => InertiaBookingsVueServiceProvider::ENTRYPOINT,
            'condition' => InertiaBookingsVueServiceProvider::VENDOR_ASSET_CONDITION,
        ])
        ->and(resolve(VendorAssetConditionRegistry::class)->passes(InertiaBookingsVueServiceProvider::VENDOR_ASSET_CONDITION, $context))->toBeTrue();
});

it('ships the shared inertia component contract for the bookings theme', function (): void {
    $basePath = dirname(__DIR__, 2);

    expect(file_exists($basePath . '/resources/js/app.js'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Page.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Pages/Capell/Bookings/Request.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Content.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Image.vue'))->toBeTrue()
        ->and(file_exists($basePath . '/resources/js/Components/Capell/Widgets/Title.vue'))->toBeTrue()
        ->and((new InertiaBookingsVueHealthCheck)->passes())->toBeTrue();
});

it('fails vue health when required component files or map entries are missing', function (): void {
    $check = new InertiaBookingsVueHealthCheck;

    expect($check->missingRequiredFiles(['resources/js/Pages/Capell/Missing.vue']))
        ->toBe(['resources/js/Pages/Capell/Missing.vue'])
        ->and($check->componentMapContainsRequiredPages())->toBeTrue();
});

it('exposes accessible booking validation and slot loading states for screenshot capture', function (): void {
    $component = (string) file_get_contents(dirname(__DIR__, 2) . '/resources/js/Pages/Capell/Bookings/Request.vue');

    expect($component)->toContain('class="error"')
        ->and($component)->toContain('role="alert"')
        ->and($component)->toContain(':aria-invalid="hasError(')
        ->and($component)->toContain(':aria-describedby="describedBy(')
        ->and($component)->toContain('class="field slots"')
        ->and($component)->toContain('class="slots-loading"')
        ->and($component)->toContain('role="status"')
        ->and($component)->toContain('form.processing')
        ->and($component)->toContain('Sending request...');
});

it('keeps raw html rendering limited to sanitized server prop boundaries', function (): void {
    $basePath = dirname(__DIR__, 2);
    $pageComponent = (string) file_get_contents($basePath . '/resources/js/Pages/Capell/Page.vue');
    $contentWidget = (string) file_get_contents($basePath . '/resources/js/Components/Capell/Widgets/Content.vue');
    $sanitizer = (string) file_get_contents($basePath . '/resources/js/Support/publicHtml.js');

    expect($pageComponent)->toContain('v-html="sanitizePublicHtml(page.content)"')
        ->and($pageComponent)->toContain('v-if="typeof page.content === \'string\'"')
        ->and($contentWidget)->toContain('v-html="sanitizePublicHtml(widget.data.content)"')
        ->and(substr_count($pageComponent . $contentWidget, 'v-html='))->toBe(2)
        ->and($sanitizer)->toContain('data-capell-authoring')
        ->and($sanitizer)->toContain('signed[-_]editor')
        ->and($sanitizer)->toContain('field_path')
        ->and($sanitizer)->toContain('model_id')
        ->and($sanitizer)->toContain('permission')
        ->and($sanitizer)->toContain('capell-app\\/[a-z0-9-]+')
        ->and($sanitizer)->toContain('javascript:')
        ->and($sanitizer)->toContain('signature')
        ->and($sanitizer)->toContain('admin');
});
