<?php

declare(strict_types=1);

use Capell\Core\Actions\BuildThemeMetadataAction;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Theme;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\FoundationTheme\Support\Assets\FoundationThemeAssetContributor;
use Capell\Frontend\Actions\ResolveFrontendResourcePlanAction;
use Capell\Frontend\Contracts\FrontendResourcePlanRenderer;
use Capell\Frontend\Data\FrontendResourceContextData;
use Capell\Frontend\Data\FrontendRuntimeManifestData;
use Capell\Frontend\Enums\RenderingStrategyEnum;
use Capell\Frontend\Support\Assets\ThemeMetaAssetContributor;
use Capell\Tests\Support\RenderedHtml;
use Capell\ThemeAgency\AgencyThemeServiceProvider;
use Capell\ThemeAwards\AwardsThemeServiceProvider;
use Illuminate\Support\Facades\File;

it('links the active theme stylesheet once when only compiled split assets are installed', function (string $themeKey, bool $legacyAssets): void {
    CapellCore::forcePackageInstalled('capell-app/theme-foundation');
    CapellCore::forcePackageInstalled('capell-app/theme-' . $themeKey);
    $provider = match ($themeKey) {
        'agency' => new AgencyThemeServiceProvider(app()),
        'awards' => new AwardsThemeServiceProvider(app()),
        default => throw new InvalidArgumentException('Unsupported theme fixture: ' . $themeKey),
    };
    $provider->register();
    $provider->boot(resolve(ThemeRegistry::class));

    $directory = storage_path('framework/testing/theme-resource-plan-' . uniqid());
    $buildDirectory = 'build-testing-theme-resource-plan-' . uniqid();
    // Vite's manifest can survive deployment without the generated CSS sources.
    // The Foundation contributor requires those sources; metadata must still link CSS.
    File::ensureDirectoryExists(public_path($buildDirectory));
    File::put(public_path($buildDirectory . '/manifest.json'), json_encode([
        'resources/css/capell/frontend.css' => ['file' => 'assets/frontend-test.css'],
        'resources/css/capell/themes/' . $themeKey . '.css' => ['file' => 'assets/' . $themeKey . '-test.css'],
    ], JSON_THROW_ON_ERROR));
    config()->set('capell-theme-foundation.tailwind.split_theme_css', true);
    config()->set('capell-theme-foundation.tailwind.theme_css_output_directory', $directory . '/sources');

    try {
        $theme = new Theme([
            'key' => $themeKey,
            'meta' => BuildThemeMetadataAction::run(
                themeKey: $themeKey,
                existingMeta: $legacyAssets ? ['assets' => ['resources/css/capell/frontend.css']] : [],
                assetsPath: $buildDirectory,
            ),
        ]);
        $context = new FrontendResourceContextData(
            page: null,
            site: null,
            language: null,
            layout: null,
            theme: $theme,
            runtime: FrontendRuntimeManifestData::forRenderingStrategy(RenderingStrategyEnum::BladeOnly),
        );
        $foundationResources = resolve(FoundationThemeAssetContributor::class)->resources($context);
        $plan = ResolveFrontendResourcePlanAction::run([
            ...$foundationResources,
            ...resolve(ThemeMetaAssetContributor::class)->resources($context),
        ]);
        $rendered = resolve(FrontendResourcePlanRenderer::class)->render($plan, $context);
        $document = new RenderedHtml($rendered->headHtml);
        $stylesheet = asset('vendor/capell/themes/' . $themeKey . '.css');
        $inactiveTheme = $themeKey === 'agency' ? 'awards' : 'agency';

        expect(collect($foundationResources)->pluck('resource.handle')->all())
            ->toBe(['capell-app/theme-foundation:frontend-style'])
            ->and(collect($plan->headResources)->pluck('url')->all())->toBe([
                asset($buildDirectory . '/assets/frontend-test.css'),
                $stylesheet,
            ])
            ->and($document->count('//link[@rel="stylesheet" and @href="' . $stylesheet . '"]'))->toBe(1)
            ->and($document->count('//link[@rel="stylesheet"]'))->toBe(2)
            ->and($document->count('//link[contains(@href, "/' . $inactiveTheme . '")]'))->toBe(0)
            ->and($plan->bodyEndResources)->toBe([]);
    } finally {
        File::deleteDirectory(public_path($buildDirectory));
        File::deleteDirectory($directory);
    }
})->with(['agency', 'awards'])->with(['fresh metadata' => false, 'legacy shared stylesheet' => true]);
