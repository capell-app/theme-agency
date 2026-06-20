<?php

declare(strict_types=1);

use Capell\FoundationTheme\Support\Demo\ThemeDemoPageDefinition;
use Capell\FoundationTheme\Support\Demo\ThemeDemoPageInstaller;

it('adds premium demo page surfaces for every prompt-built theme', function (string $themeKey, string $themeName): void {
    $installer = new ThemeDemoPageInstaller;
    $method = new ReflectionMethod($installer, 'definitions');

    /** @var list<ThemeDemoPageDefinition> $definitions */
    $definitions = $method->invoke($installer, $themeKey, $themeName, 'https://example.test');
    $surfaces = array_map(static fn (ThemeDemoPageDefinition $definition): string => $definition->surface, $definitions);
    $premiumDefinitions = array_values(array_filter(
        $definitions,
        static fn (ThemeDemoPageDefinition $definition): bool => ! in_array($definition->surface, [
            'homepage',
            'directory',
            'detail',
            'contact',
            'empty',
            'not-found',
            'cta',
        ], true),
    ));

    expect($definitions)->toHaveCount(10)
        ->and($premiumDefinitions)->toHaveCount(3)
        ->and($surfaces)->not->toContain('premium-1', 'premium-2', 'premium-3');

    foreach ($premiumDefinitions as $definition) {
        $heroHeading = data_get($definition->renderData, 'hero.heading');

        expect($definition->renderData['sections'] ?? null)
            ->toBeArray()
            ->toHaveCount(3)
            ->and($definition->renderData['items'] ?? null)
            ->toBeArray()
            ->toHaveCount(3)
            ->and($heroHeading)
            ->toBeString()
            ->and($definition->title)
            ->toEndWith(is_string($heroHeading) && $heroHeading !== '' ? $heroHeading : 'x');
    }
})->with([
    ['ai-lab', 'AI Lab'],
    ['api-platform', 'API Platform'],
    ['ai-agent', 'AI Agent'],
    ['aeo-analytics', 'AEO Analytics'],
    ['fintech-trust', 'Fintech Trust'],
    ['crypto-defi', 'Crypto DeFi'],
    ['quant-trading', 'Quant Trading'],
    ['devtool-oss', 'Devtool OSS'],
    ['robotics-hardware', 'Robotics Hardware'],
    ['manufacturing', 'Manufacturing'],
    ['packaging-supplier', 'Packaging Supplier'],
    ['conference-event', 'Conference Event'],
    ['podcast-show', 'Podcast Show'],
    ['newsroom-magazine', 'Newsroom Magazine'],
    ['design-studio', 'Design Studio'],
    ['product-studio', 'Product Studio'],
    ['personal-dev', 'Personal Dev'],
    ['creator-newsletter', 'Creator Newsletter'],
    ['law-firm', 'Law Firm'],
    ['financial-advisory', 'Financial Advisory'],
    ['construction-trades', 'Construction Trades'],
    ['fitness-wellness', 'Fitness Wellness'],
    ['beauty-spa', 'Beauty & Spa'],
    ['travel-tourism', 'Travel Tourism'],
    ['automotive-dealer', 'Automotive Dealer'],
    ['property-developer', 'Property Developer'],
    ['recruitment-jobs', 'Recruitment Jobs'],
    ['editorial-serif', 'Editorial Serif'],
]);
