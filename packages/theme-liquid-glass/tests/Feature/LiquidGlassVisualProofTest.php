<?php

declare(strict_types=1);

use Capell\Core\ThemeStudio\Data\BrandProfileData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\NavigationData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Data\ThemePresetData;
use Capell\ThemeStudio\LiquidGlass\LiquidGlassThemeServiceProvider;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\View;

require_once dirname(__DIR__, 4) . '/tests/Packages/Support/ThemeDemoLayoutScreenshots.php';

it('captures Liquid Glass preset and mobile visual proof from rendered package views', function (): void {
    View::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/views');
    Lang::addNamespace('capell-theme-liquid-glass', __DIR__ . '/../../resources/lang');

    $entries = liquidGlassVisualProofEntries();
    $manifestEntries = [];

    foreach ($entries as $entry) {
        $html = liquidGlassVisualProofHtml($entry['preset']);

        expect($html)
            ->toContain('liquid-glass-shell')
            ->toContain('liquid-glass-menu-trigger')
            ->toContain('style="--theme-primary:' . $entry['primaryColor'])
            ->toContain('--theme-accent:' . $entry['accentColor'])
            ->toContain('--theme-surface:' . $entry['surfaceColor'])
            ->toContain('--theme-foreground:' . $entry['foregroundColor'])
            ->not->toContain('capell-app/theme-liquid-glass')
            ->not->toContain('authoring');

        file_put_contents($entry['htmlPath'], $html);

        $manifestEntries[] = [
            'surface' => $entry['surface'],
            'type' => 'theme-preset',
            'layout' => $entry['layout'],
            'htmlPath' => $entry['htmlPath'],
            'screenshotPath' => $entry['screenshotPath'],
            'viewport' => $entry['viewport'],
        ];
    }

    if (getenv('CAPELL_REFRESH_LIQUID_GLASS_VISUAL_PROOF') === '1') {
        $result = runThemeDemoScreenshotCapture(LiquidGlassThemeServiceProvider::THEME_KEY, [
            'viewport' => ['width' => 960, 'height' => 1200],
            'concurrency' => 2,
            'entries' => $manifestEntries,
        ]);

        expect($result['entries'])->toHaveCount(count($manifestEntries));

        foreach ($result['entries'] as $resultEntry) {
            expect($resultEntry['blank'])->toBeFalse()
                ->and($resultEntry['horizontalOverflow'])->toBeFalse(
                    'Screenshot has top-level horizontal overflow: ' . json_encode($resultEntry['overflowingElements'] ?? [], JSON_THROW_ON_ERROR),
                );
        }
    }

    foreach ($entries as $entry) {
        expect($entry['screenshotPath'])->toBeFile();

        $dimensions = getimagesize($entry['screenshotPath']);

        expect($dimensions)->toBeArray()
            ->and($dimensions[0] ?? null)->toBe($entry['viewport']['width'])
            ->and($dimensions[1] ?? null)->toBeGreaterThanOrEqual($entry['viewport']['height']);
    }
});

/**
 * @return list<array{
 *     surface: string,
 *     layout: string,
 *     preset: ThemePresetData,
 *     primaryColor: string,
 *     accentColor: string,
 *     surfaceColor: string,
 *     foregroundColor: string,
 *     htmlPath: string,
 *     screenshotPath: string,
 *     viewport: array{width: int, height: int}
 * }>
 */
function liquidGlassVisualProofEntries(): array
{
    $definition = LiquidGlassThemeServiceProvider::definition();
    $desktopViewport = ['width' => 960, 'height' => 1200];
    $mobileViewport = ['width' => 390, 'height' => 1200];

    return collect($definition->presets)
        ->map(function (ThemePresetData $preset) use ($desktopViewport, $mobileViewport): array {
            $presetValues = $preset->values;
            $isMobile = $preset->key === 'graphite';
            $surface = $isMobile ? 'graphite-mobile' : $preset->key . '-preset';

            return [
                'surface' => $surface,
                'layout' => $isMobile ? 'mobile' : 'desktop',
                'preset' => $preset,
                'primaryColor' => liquidGlassVisualProofColor($presetValues, 'primaryColor'),
                'accentColor' => liquidGlassVisualProofColor($presetValues, 'accentColor'),
                'surfaceColor' => liquidGlassVisualProofColor($presetValues, 'surfaceColor'),
                'foregroundColor' => liquidGlassVisualProofColor($presetValues, 'foregroundColor'),
                'htmlPath' => themeDemoScreenshotHtmlPath(
                    LiquidGlassThemeServiceProvider::THEME_KEY,
                    $surface,
                    'theme-preset',
                    $isMobile ? 'mobile' : 'desktop',
                ),
                'screenshotPath' => dirname(__DIR__, 2) . '/docs/screenshots/liquid-glass-' . $surface . '.png',
                'viewport' => $isMobile ? $mobileViewport : $desktopViewport,
            ];
        })
        ->values()
        ->all();
}

/**
 * @param  array<string, mixed>  $values
 */
function liquidGlassVisualProofColor(array $values, string $key): string
{
    $value = $values[$key] ?? null;

    if (! is_string($value) || $value === '') {
        throw new RuntimeException(sprintf('Liquid Glass preset value [%s] must be a non-empty string.', $key));
    }

    return $value;
}

function liquidGlassVisualProofHtml(ThemePresetData $preset): string
{
    $brand = new BrandProfileData(
        primaryColor: liquidGlassVisualProofColor($preset->values, 'primaryColor'),
        accentColor: liquidGlassVisualProofColor($preset->values, 'accentColor'),
        surfaceColor: liquidGlassVisualProofColor($preset->values, 'surfaceColor'),
        foregroundColor: liquidGlassVisualProofColor($preset->values, 'foregroundColor'),
    );

    $content = implode("\n", [
        view('capell-theme-liquid-glass::sections.navigation', [
            'section' => new NavigationData(
                brandName: 'Liquid Glass',
                items: [
                    ['label' => 'Sections', 'url' => '#content'],
                    ['label' => 'Proof', 'url' => '#proof'],
                    ['label' => 'Contact', 'url' => '#contact'],
                ],
                ctaLabel: 'Preview',
                ctaUrl: '#main-content',
            ),
        ])->render(),
        view('capell-theme-liquid-glass::sections.hero', [
            'section' => HeroSectionData::from([
                'eyebrow' => $preset->name . ' preset',
                'heading' => $preset->name . ' keeps glass surfaces legible',
                'summary' => $preset->description,
                'actions' => [
                    ['label' => 'Review sections', 'url' => '#content', 'style' => 'primary'],
                    ['label' => 'View proof', 'url' => '#proof', 'style' => 'secondary'],
                ],
                'stats' => [
                    ['label' => 'Primary', 'value' => liquidGlassVisualProofColor($preset->values, 'primaryColor')],
                    ['label' => 'Accent', 'value' => liquidGlassVisualProofColor($preset->values, 'accentColor')],
                    ['label' => 'Surface', 'value' => liquidGlassVisualProofColor($preset->values, 'surfaceColor')],
                ],
            ]),
        ])->render(),
        view('capell-theme-liquid-glass::sections.features', [
            'section' => FeatureSectionData::from([
                'heading' => 'Token-driven section rhythm',
                'summary' => 'Cards, buttons, text, and translucent panels stay inside the same theme token contract.',
                'features' => [
                    ['type' => 'Navigation', 'title' => 'Mobile menu included', 'description' => 'The compact menu uses the same glass panel and action styles as the desktop shell.'],
                    ['type' => 'Surface', 'title' => 'Readable panels', 'description' => 'Foreground, border, and panel colors are derived from the active preset values.'],
                    ['type' => 'Actions', 'title' => 'High-contrast CTAs', 'description' => 'Primary and secondary actions remain visible against light and dark surfaces.'],
                ],
            ]),
        ])->render(),
        view('capell-theme-liquid-glass::sections.proof', [
            'section' => ProofSectionData::from([
                'heading' => 'Preset proof points',
                'summary' => 'Each preview uses the package views and Theme Studio colors rather than a hand-drawn marketing asset.',
                'items' => [
                    ['metric' => '3', 'quote' => 'Theme presets render through one Blade shell.', 'name' => 'Preset coverage', 'role' => 'Clarity, Prism, Graphite'],
                    ['metric' => '390px', 'quote' => 'Graphite proves the compact navigation and dark surface.', 'name' => 'Mobile proof', 'role' => 'Small viewport'],
                    ['metric' => '0', 'quote' => 'No private admin markers appear in public output.', 'name' => 'Public safety', 'role' => 'Cache-safe rendering'],
                ],
            ]),
        ])->render(),
        view('capell-theme-liquid-glass::sections.cta', [
            'section' => CtaSectionData::from([
                'heading' => 'Use ' . $preset->name . ' for a polished public shell',
                'summary' => 'The visual system remains package-owned while content, routes, and public HTML stay controlled by the host app.',
                'actions' => [
                    ['label' => 'Start with Liquid Glass', 'url' => '#main-content', 'style' => 'primary'],
                    ['label' => 'Compare presets', 'url' => '#proof', 'style' => 'secondary'],
                ],
            ]),
        ])->render(),
    ]);

    return view('capell-theme-liquid-glass::page', [
        'brand' => $brand,
        'content' => $content,
    ])->render();
}
