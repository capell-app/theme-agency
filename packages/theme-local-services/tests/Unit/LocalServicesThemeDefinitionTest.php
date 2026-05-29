<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\Core\ThemeStudio\Data\ContentListingSectionData;
use Capell\Core\ThemeStudio\Data\CtaSectionData;
use Capell\Core\ThemeStudio\Data\FeatureSectionData;
use Capell\Core\ThemeStudio\Data\HeroSectionData;
use Capell\Core\ThemeStudio\Data\ProofSectionData;
use Capell\Core\ThemeStudio\Theme\ThemeRegistry;
use Capell\Tests\Packages\PackagesTestCase;
use Capell\ThemeStudio\LocalServices\LocalServicesThemeServiceProvider;

uses(PackagesTestCase::class);

it('defines the Local Services theme contract', function (): void {
    $definition = LocalServicesThemeServiceProvider::definition();

    expect($definition->key)->toBe('local-services')
        ->and($definition->package)->toBe('capell-app/theme-local-services')
        ->and($definition->extends)->toBe('default')
        ->and($definition->includedSections)->toContain('hero')
        ->and($definition->includedSections)->toContain('features')
        ->and($definition->includedSections)->toContain('proof')
        ->and($definition->includedSections)->toContain('content-listing')
        ->and($definition->includedSections)->toContain('cta')
        ->and($definition->includedSections)->toContain('footer')
        ->and($definition->presets)->toHaveCount(1);
});

it('renders standard sections through Local Services views', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $featureHtml = $registry
        ->sectionRenderer('local-services', 'features')
        ->render(new FeatureSectionData(
            heading: 'Quote-ready service routes',
            summary: 'Feature cards should look like local jobs and estimate paths.',
            features: [
                ['title' => 'Rapid estimate triage', 'summary' => 'Match the right team to each enquiry.', 'type' => 'Dispatch'],
            ],
        ));

    $proofHtml = $registry
        ->sectionRenderer('local-services', 'proof')
        ->render(ProofSectionData::from([
            'heading' => 'Local proof board',
            'summary' => 'Proof should use service metrics.',
            'items' => [
                ['metric' => '24h', 'label' => 'Response', 'summary' => 'Most enquiries receive a fast first reply.'],
            ],
        ]));

    $listingHtml = $registry
        ->sectionRenderer('local-services', 'content-listing')
        ->render(new ContentListingSectionData(
            heading: 'Service route cards',
            summary: 'Listings should carry local area and availability cues.',
            items: [
                ['title' => 'Boiler repair', 'summary' => 'Urgent coverage across nearby districts.', 'type' => 'Repair'],
            ],
        ));

    $ctaHtml = $registry
        ->sectionRenderer('local-services', 'cta')
        ->render(new CtaSectionData(
            heading: 'Book the next service slot',
            summary: 'Move visitors from scope to confirmed work.',
            actions: [['label' => 'Request quote', 'url' => '#quote', 'style' => 'primary']],
        ));

    expect($featureHtml)
        ->toContain('Quote-ready service routes')
        ->toContain('Dispatch board')
        ->toContain('Quote')
        ->not->toContain('capell-app/theme-local-services');

    expect($proofHtml)
        ->toContain('Local proof board')
        ->toContain('Local proof')
        ->toContain('Route board')
        ->toContain('Arrival window')
        ->toContain('Completion proof')
        ->toContain('24h')
        ->not->toContain('capell-app/theme-local-services');

    expect($listingHtml)
        ->toContain('Service route cards')
        ->toContain('Boiler repair')
        ->toContain('Open slot')
        ->not->toContain('capell-app/theme-local-services');

    expect($ctaHtml)
        ->toContain('Book the next service slot')
        ->toContain('Bookable work')
        ->toContain('Request quote')
        ->not->toContain('capell-app/theme-local-services');
});

it('renders hydrated hero data through the Local Services hero view', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled(LocalServicesThemeServiceProvider::$packageName);

    $registry = new ThemeRegistry;
    (new LocalServicesThemeServiceProvider($this->app))->boot($registry);

    $renderer = $registry->sectionRenderer('local-services', 'hero');

    expect($renderer)->not->toBeNull();

    $html = $renderer->render(HeroSectionData::from([
        'heading' => 'Book a service team this week',
        'summary' => 'Hydrated local services hero summary.',
        'actions' => [
            ['label' => 'Request a quote', 'url' => '#quote'],
            ['label' => 'View service areas', 'url' => '#areas'],
        ],
    ]));

    expect($html)
        ->toContain('Quote desk')
        ->toContain('Book a service team this week')
        ->toContain('Hydrated local services hero summary.')
        ->toContain('Request a quote')
        ->toContain('View service areas')
        ->toContain('Live route board')
        ->not->toContain('capell-app/theme-local-services');
});
