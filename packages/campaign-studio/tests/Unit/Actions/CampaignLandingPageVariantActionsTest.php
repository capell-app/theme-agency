<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\BuildCampaignLandingPageVariantsAction;
use Capell\CampaignStudio\Actions\ResolveCampaignLandingPageVariantAction;
use Capell\CampaignStudio\Data\AudienceTargetData;
use Capell\CampaignStudio\Enums\LandingPageVariantMatchType;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Core\Models\Page;
use Carbon\CarbonImmutable;

function campaignStudioPublishedPageId(): int
{
    return (int) Page::factory()
        ->create([
            'visible_from' => CarbonImmutable::now()->subDay(),
            'visible_until' => null,
        ])
        ->getKey();
}

it('normalizes audience targeting data from campaign urls', function (): void {
    $audienceTarget = AudienceTargetData::fromUrl(
        'https://capell.test/landing?utm_source=&utm_medium=email&utm_campaign=spring&utm_term=enterprise&utm_content=hero-b',
    );

    expect($audienceTarget->utmSource)->toBeNull()
        ->and($audienceTarget->utmMedium)->toBe('email')
        ->and($audienceTarget->utmCampaign)->toBe('spring')
        ->and($audienceTarget->utmTerm)->toBe('enterprise')
        ->and($audienceTarget->utmContent)->toBe('hero-b');
});

it('builds landing page variants from campaign landing pages with stable variant keys', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    $secondaryLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'utm_content' => null,
            'utm_term' => 'enterprise',
            'is_primary' => false,
        ]);
    $primaryLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'utm_content' => 'hero-a',
            'utm_term' => 'startup',
            'is_primary' => true,
        ]);

    $variants = BuildCampaignLandingPageVariantsAction::run($campaignGroup);

    expect($variants)->toHaveCount(2)
        ->and($variants[0]->landingPageId)->toBe($primaryLandingPage->getKey())
        ->and($variants[0]->variantKey)->toBe('hero-a')
        ->and($variants[0]->isPrimary)->toBeTrue()
        ->and($variants[1]->landingPageId)->toBe($secondaryLandingPage->getKey())
        ->and($variants[1]->variantKey)->toBe('enterprise')
        ->and($variants[1]->isPrimary)->toBeFalse();
});

it('resolves utm content targeted variants before utm term and primary fallbacks', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => null,
            'utm_term' => null,
            'is_primary' => true,
        ]);
    CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => null,
            'utm_term' => 'enterprise',
            'is_primary' => false,
        ]);
    $contentLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => 'hero-b',
            'utm_term' => 'enterprise',
            'is_primary' => false,
        ]);

    $selection = ResolveCampaignLandingPageVariantAction::run(
        $campaignGroup,
        AudienceTargetData::fromUrl('https://capell.test/?utm_content=hero-b&utm_term=enterprise'),
    );

    expect($selection)->not->toBeNull()
        ->and($selection->variant->landingPageId)->toBe($contentLandingPage->getKey())
        ->and($selection->matchType)->toBe(LandingPageVariantMatchType::UtmContent)
        ->and($selection->matchedValue)->toBe('hero-b');
});

it('resolves utm term targeted variants when content has no matching landing page', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    $termLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => null,
            'utm_term' => 'enterprise',
            'is_primary' => false,
        ]);

    $selection = ResolveCampaignLandingPageVariantAction::run(
        $campaignGroup,
        new AudienceTargetData(utmTerm: 'enterprise', utmContent: 'missing-content'),
    );

    expect($selection)->not->toBeNull()
        ->and($selection->variant->landingPageId)->toBe($termLandingPage->getKey())
        ->and($selection->matchType)->toBe(LandingPageVariantMatchType::UtmTerm)
        ->and($selection->matchedValue)->toBe('enterprise');
});

it('falls back to primary then first available landing pages for unqualified audiences', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    $firstLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => null,
            'utm_term' => null,
            'is_primary' => false,
        ]);
    $primaryLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => null,
            'utm_term' => null,
            'is_primary' => true,
        ]);

    $primarySelection = ResolveCampaignLandingPageVariantAction::run($campaignGroup, new AudienceTargetData);

    $primaryLandingPage->update(['is_primary' => false]);

    $firstAvailableSelection = ResolveCampaignLandingPageVariantAction::run($campaignGroup->refresh(), new AudienceTargetData);

    expect($primarySelection)->not->toBeNull()
        ->and($primarySelection->variant->landingPageId)->toBe($primaryLandingPage->getKey())
        ->and($primarySelection->matchType)->toBe(LandingPageVariantMatchType::Primary)
        ->and($firstAvailableSelection)->not->toBeNull()
        ->and($firstAvailableSelection->variant->landingPageId)->toBe($firstLandingPage->getKey())
        ->and($firstAvailableSelection->matchType)->toBe(LandingPageVariantMatchType::FirstAvailable);
});

it('skips targeted variants when their linked pages are not published', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => (int) Page::factory()
                ->create([
                    'visible_from' => CarbonImmutable::now()->subDays(3),
                    'visible_until' => CarbonImmutable::now()->subDay(),
                ])
                ->getKey(),
            'utm_content' => 'hero-b',
            'utm_term' => null,
            'is_primary' => true,
        ]);
    $publishedLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => 'hero-b',
            'utm_term' => null,
            'is_primary' => false,
        ]);

    $selection = ResolveCampaignLandingPageVariantAction::run(
        $campaignGroup,
        new AudienceTargetData(utmContent: 'hero-b'),
    );

    expect($selection)->not->toBeNull()
        ->and($selection->variant->landingPageId)->toBe($publishedLandingPage->getKey())
        ->and($selection->matchType)->toBe(LandingPageVariantMatchType::UtmContent);
});

it('skips unpublished primary and fallback variants for unqualified audiences', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();
    CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => (int) Page::factory()
                ->create([
                    'visible_from' => CarbonImmutable::now()->subDays(3),
                    'visible_until' => CarbonImmutable::now()->subDay(),
                ])
                ->getKey(),
            'utm_content' => null,
            'utm_term' => null,
            'is_primary' => true,
        ]);
    CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => (int) Page::factory()
                ->create([
                    'visible_from' => CarbonImmutable::now()->addDay(),
                    'visible_until' => null,
                ])
                ->getKey(),
            'utm_content' => null,
            'utm_term' => null,
            'is_primary' => false,
        ]);
    $publishedLandingPage = CampaignLandingPage::factory()
        ->for($campaignGroup, 'campaignGroup')
        ->create([
            'page_id' => campaignStudioPublishedPageId(),
            'utm_content' => null,
            'utm_term' => null,
            'is_primary' => false,
        ]);

    $selection = ResolveCampaignLandingPageVariantAction::run($campaignGroup, new AudienceTargetData);

    expect($selection)->not->toBeNull()
        ->and($selection->variant->landingPageId)->toBe($publishedLandingPage->getKey())
        ->and($selection->matchType)->toBe(LandingPageVariantMatchType::FirstAvailable);
});

it('returns null when a campaign has no landing page variants', function (): void {
    $campaignGroup = CampaignGroup::factory()->create();

    $selection = ResolveCampaignLandingPageVariantAction::run($campaignGroup, new AudienceTargetData);

    expect($selection)->toBeNull();
});
