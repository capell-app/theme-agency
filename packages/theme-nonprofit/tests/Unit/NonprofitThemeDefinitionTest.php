<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\Nonprofit\NonprofitThemeServiceProvider;

uses(PackagesTestCase::class);

it('defines the Nonprofit theme contract', function (): void {
    $definition = NonprofitThemeServiceProvider::definition();

    expect($definition->key)->toBe('nonprofit')
        ->and($definition->package)->toBe('capell-app/theme-nonprofit')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard sections through Nonprofit views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(NonprofitThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new NonprofitThemeServiceProvider($this->app))->boot($registry);

    $featureHtml = $registry
        ->sectionRenderer('nonprofit', 'features')
        ->render(new FeatureSectionData(
            heading: 'Impact pathways',
            summary: 'Supporter cards should feel specific to nonprofit work.',
            features: [
                ['title' => 'Campaign paths', 'summary' => 'Move supporters from belief to action.', 'type' => 'Campaigns'],
            ],
        ));

    $listingHtml = $registry
        ->sectionRenderer('nonprofit', 'content-listing')
        ->render(new ContentListingSectionData(
            heading: 'Community stories',
            summary: 'Cards should support campaigns and proof.',
            items: [
                ['title' => 'Neighbourhood appeal', 'summary' => 'Show a campaign outcome.', 'type' => 'Appeal'],
            ],
        ));

    $ctaHtml = $registry
        ->sectionRenderer('nonprofit', 'cta')
        ->render(new CtaSectionData(
            heading: 'Back the next campaign',
            summary: 'Move supporters into action.',
            actions: [['label' => 'Donate now', 'url' => '#donate', 'style' => 'primary']],
        ));

    expect($featureHtml)
        ->toContain('Impact pathways')
        ->toContain('Impact paths')
        ->toContain('Donor ready')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($listingHtml)
        ->toContain('Community stories')
        ->toContain('Neighbourhood appeal')
        ->toContain('Supporter ready')
        ->not->toContain('capell-app/theme-nonprofit');

    expect($ctaHtml)
        ->toContain('Back the next campaign')
        ->toContain('Supporter action')
        ->toContain('Donate now')
        ->not->toContain('capell-app/theme-nonprofit');
});
