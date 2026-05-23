<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Saas;

use Capell\Core\Enums\FrontendRuntime;
use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ThemeDefinitionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\Core\ThemeStudio\Rendering\BladeThemeRenderer;
use Capell\Core\ThemeStudio\Rendering\ViewSectionRenderer;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Illuminate\Support\ServiceProvider;
use Override;

class SaasThemeServiceProvider extends ServiceProvider
{
    public const string THEME_KEY = 'saas';

    public static string $packageName = 'capell-app/theme-saas';

    public static function definition(): ThemeDefinitionData
    {
        return new ThemeDefinitionData(
            key: self::THEME_KEY,
            name: 'SaaS',
            description: 'Conversion-led layouts with product framing, compact proof, and clear feature hierarchy.',
            package: 'capell-app/theme-saas',
            previewImage: '/vendor/capell/themes/saas-launch.jpg',
            tags: ['Product', 'Conversion', 'Growth'],
            bestFit: ['Software products', 'Startups', 'Subscription services'],
            includedSections: ['navigation', 'hero', 'features', 'proof', 'content-listing', 'cta', 'footer'],
            presets: [
                new ThemePresetData(
                    key: 'launch',
                    name: 'Launch',
                    description: 'High-conversion product framing with crisp cards and proof near the fold.',
                    previewImage: '/vendor/capell/themes/saas-launch.jpg',
                    values: [
                        'primaryColor' => '#6366f1',
                        'accentColor' => '#10b981',
                        'headingFont' => 'inter',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                    ],
                ),
                new ThemePresetData(
                    key: 'platform',
                    name: 'Platform',
                    description: 'Enterprise SaaS positioning with denser proof and measured motion.',
                    previewImage: '/vendor/capell/themes/saas-platform.jpg',
                    values: [
                        'primaryColor' => '#2563eb',
                        'accentColor' => '#14b8a6',
                        'headingFont' => 'manrope',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'standard',
                        'motionIntensity' => 'subtle',
                    ],
                ),
                new ThemePresetData(
                    key: 'labs',
                    name: 'Labs',
                    description: 'More expressive product storytelling for AI, developer, and beta products.',
                    previewImage: '/vendor/capell/themes/saas-labs.jpg',
                    values: [
                        'primaryColor' => '#7c3aed',
                        'accentColor' => '#22d3ee',
                        'headingFont' => 'sora',
                        'cardStyle' => 'layered',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                    ],
                ),
                new ThemePresetData(
                    key: 'momentum',
                    name: 'Momentum',
                    description: 'Electric startup growth palette from the Stitch Momentum Growth design system.',
                    previewImage: '/vendor/capell/themes/saas-momentum.jpg',
                    values: [
                        'primaryColor' => '#4f46e5',
                        'accentColor' => '#fe5e1e',
                        'neutralColor' => '#131b2e',
                        'surfaceColor' => '#faf8ff',
                        'foregroundColor' => '#131b2e',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'lg',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'velocity',
                    name: 'Velocity',
                    description: 'Product-led conversion direction from the Stitch Velocity landing, article, blog, and feature screens.',
                    previewImage: '/vendor/capell/themes/saas-velocity.jpg',
                    values: [
                        'primaryColor' => '#2563eb',
                        'accentColor' => '#06b6d4',
                        'neutralColor' => '#0f172a',
                        'surfaceColor' => '#f8fafc',
                        'foregroundColor' => '#0f172a',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'md',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'compact',
                    ],
                ),
                new ThemePresetData(
                    key: 'startup-growth',
                    name: 'Startup Growth',
                    description: 'Warm, fast-moving growth-site treatment from the Stitch Startup Growth homepage.',
                    previewImage: '/vendor/capell/themes/saas-startup-growth.jpg',
                    values: [
                        'primaryColor' => '#16a34a',
                        'accentColor' => '#f97316',
                        'neutralColor' => '#14532d',
                        'surfaceColor' => '#fffaf5',
                        'foregroundColor' => '#1f2937',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'elevated',
                        'navigationStyle' => 'minimal',
                        'layoutPresentation' => 'editorial',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'natural',
                        'radius' => 'xl',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'spacious',
                    ],
                ),
                new ThemePresetData(
                    key: 'capell-platform',
                    name: 'Capell Platform',
                    description: 'Capell-specific SaaS homepage direction from the Stitch Capell SaaS concept.',
                    previewImage: '/vendor/capell/themes/saas-capell-platform.jpg',
                    values: [
                        'primaryColor' => '#21417f',
                        'accentColor' => '#7d5100',
                        'neutralColor' => '#1a1c1b',
                        'surfaceColor' => '#faf9f7',
                        'foregroundColor' => '#1a1c1b',
                        'headingFont' => 'manrope',
                        'bodyFont' => 'inter',
                        'spacing' => 'balanced',
                        'cardStyle' => 'bordered',
                        'navigationStyle' => 'standard',
                        'layoutPresentation' => 'structured',
                        'motionIntensity' => 'subtle',
                        'mediaTreatment' => 'framed',
                        'radius' => 'none',
                        'headingScale' => 'balanced',
                        'cardDensity' => 'comfortable',
                    ],
                ),
                new ThemePresetData(
                    key: 'nextgen',
                    name: 'NextGen',
                    description: 'AI-forward and immersive product storytelling from the Stitch NextGen homepage.',
                    previewImage: '/vendor/capell/themes/saas-nextgen.jpg',
                    values: [
                        'primaryColor' => '#7c3aed',
                        'accentColor' => '#22d3ee',
                        'neutralColor' => '#111827',
                        'surfaceColor' => '#f5f3ff',
                        'foregroundColor' => '#111827',
                        'headingFont' => 'sora',
                        'bodyFont' => 'inter',
                        'spacing' => 'spacious',
                        'cardStyle' => 'layered',
                        'navigationStyle' => 'prominent',
                        'layoutPresentation' => 'immersive',
                        'motionIntensity' => 'expressive',
                        'mediaTreatment' => 'framed',
                        'radius' => 'xl',
                        'headingScale' => 'expressive',
                        'cardDensity' => 'comfortable',
                        'overlayTreatment' => 'strong',
                    ],
                ),
            ],
            assets: ['css' => 'vendor/capell/themes/saas.css'],
            runtime: FrontendRuntime::Blade,
        );
    }

    #[Override]
    public function register(): void {}

    public function boot(ThemeRegistry $registry): void
    {
        if (! CapellCore::isPackageInstalled(self::$packageName)) {
            return;
        }

        $this->loadTranslationsFrom(__DIR__ . '/../resources/lang', 'capell-theme-saas');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'capell-theme-saas');

        $sectionRenderers = $this->sectionRenderers();

        $registry->register(
            definition: self::definition(),
            themeRenderer: new BladeThemeRenderer(
                themeKey: self::THEME_KEY,
                layoutView: 'capell-theme-saas::page',
                sectionRenderers: $sectionRenderers,
            ),
            sectionRenderers: array_values($sectionRenderers),
        );
    }

    /**
     * @return array<string, ViewSectionRenderer>
     */
    private function sectionRenderers(): array
    {
        return [
            'navigation' => new ViewSectionRenderer(self::THEME_KEY, 'navigation', 'capell-theme-saas::sections.navigation', failLoudly: true),
            'hero' => new ViewSectionRenderer(self::THEME_KEY, 'hero', 'capell-theme-saas::sections.hero', failLoudly: true),
            'features' => new ViewSectionRenderer(self::THEME_KEY, 'features', 'capell-theme-saas::sections.features', failLoudly: true),
            'proof' => new ViewSectionRenderer(self::THEME_KEY, 'proof', 'capell-theme-saas::sections.proof', failLoudly: true),
            'content-listing' => new ViewSectionRenderer(self::THEME_KEY, 'content-listing', 'capell-theme-saas::sections.content-listing', failLoudly: true),
            'cta' => new ViewSectionRenderer(self::THEME_KEY, 'cta', 'capell-theme-saas::sections.cta', failLoudly: true),
            'footer' => new ViewSectionRenderer(self::THEME_KEY, 'footer', 'capell-theme-saas::sections.footer', failLoudly: true),
        ];
    }
}
