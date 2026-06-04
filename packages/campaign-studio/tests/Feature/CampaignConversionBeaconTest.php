<?php

declare(strict_types=1);

use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignCtaWidget;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Insights\Enums\InsightsConsentRegion;
use Capell\Insights\Models\InsightsVisit;

it('registers a public campaign conversion beacon route', function (): void {
    expect(route('capell-campaigns.conversions', absolute: false))
        ->toBe('/capell/campaigns/conversions');
});

it('records page view conversions through the public beacon', function (): void {
    $campaign = CampaignGroup::factory()->create();
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['type' => ConversionGoalType::PageView]);
    $page = Page::factory()->create();
    PageUrl::factory()
        ->page($page)
        ->create([
            'site_id' => $page->site_id,
            'url' => '/spring-launch',
        ]);
    $landingPage = CampaignLandingPage::factory()
        ->for($campaign, 'campaignGroup')
        ->create([
            'page_id' => $page->getKey(),
            'primary_goal_id' => $goal->getKey(),
        ]);
    $visit = campaignBeaconVisit();

    $payload = [
        'type' => 'page_view',
        'url' => 'https://example.test/spring-launch?utm_campaign=spring-launch',
        'visit_id' => $visit->uuid,
    ];

    $this->postJson(route('capell-campaigns.conversions'), $payload)
        ->assertNoContent();
    $this->postJson(route('capell-campaigns.conversions'), $payload)
        ->assertNoContent();

    $conversion = CampaignConversion::query()->firstOrFail();

    expect(CampaignConversion::query()->count())->toBe(1);
    expect($conversion->campaign_conversion_goal_id)->toBe($goal->getKey());
    expect($conversion->campaign_landing_page_id)->toBe($landingPage->getKey());
    expect($conversion->getAttribute('insights_visit_id'))->toBe($visit->getKey());
});

it('records cta click conversions through the public beacon', function (): void {
    $campaign = CampaignGroup::factory()->create();
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create([
            'key' => 'book-demo',
            'type' => ConversionGoalType::CtaClick,
        ]);
    $page = Page::factory()->create();
    PageUrl::factory()
        ->page($page)
        ->create([
            'site_id' => $page->site_id,
            'url' => '/demo',
        ]);
    $landingPage = CampaignLandingPage::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['page_id' => $page->getKey()]);
    $ctaWidget = CampaignCtaWidget::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['key' => 'hero-cta']);
    $visit = campaignBeaconVisit();

    $payload = [
        'type' => 'cta_click',
        'url' => 'https://example.test/demo',
        'goal_key' => 'book-demo',
        'cta_key' => 'hero-cta',
        'visit_id' => $visit->uuid,
    ];

    $this->postJson(route('capell-campaigns.conversions'), $payload)
        ->assertNoContent();
    $this->postJson(route('capell-campaigns.conversions'), $payload)
        ->assertNoContent();

    $conversion = CampaignConversion::query()->firstOrFail();

    expect(CampaignConversion::query()->count())->toBe(1);
    expect($conversion->campaign_conversion_goal_id)->toBe($goal->getKey());
    expect($conversion->campaign_landing_page_id)->toBe($landingPage->getKey());
    expect($conversion->getAttribute('insights_visit_id'))->toBe($visit->getKey());
    expect($conversion->source_id)->toBe($ctaWidget->getKey());
});

it('scopes cta click beacon goals to the campaign resolved from the submitted url', function (): void {
    $firstCampaign = CampaignGroup::factory()->create(['slug' => 'spring-a']);
    $secondCampaign = CampaignGroup::factory()->create(['slug' => 'spring-b']);
    $firstGoal = CampaignConversionGoal::factory()
        ->for($firstCampaign, 'campaignGroup')
        ->create([
            'key' => 'book-demo',
            'type' => ConversionGoalType::CtaClick,
        ]);
    $secondGoal = CampaignConversionGoal::factory()
        ->for($secondCampaign, 'campaignGroup')
        ->create([
            'key' => 'book-demo',
            'type' => ConversionGoalType::CtaClick,
        ]);
    CampaignCtaWidget::factory()
        ->for($firstCampaign, 'campaignGroup')
        ->create(['key' => 'hero-cta']);
    $secondCtaWidget = CampaignCtaWidget::factory()
        ->for($secondCampaign, 'campaignGroup')
        ->create(['key' => 'hero-cta']);
    $firstPage = Page::factory()->create();
    $secondPage = Page::factory()->create();
    PageUrl::factory()
        ->page($firstPage)
        ->create([
            'site_id' => $firstPage->site_id,
            'url' => '/spring-a',
        ]);
    PageUrl::factory()
        ->page($secondPage)
        ->create([
            'site_id' => $secondPage->site_id,
            'url' => '/spring-b',
        ]);
    CampaignLandingPage::factory()
        ->for($firstCampaign, 'campaignGroup')
        ->create(['page_id' => $firstPage->getKey()]);
    $secondLandingPage = CampaignLandingPage::factory()
        ->for($secondCampaign, 'campaignGroup')
        ->create(['page_id' => $secondPage->getKey()]);
    $visit = campaignBeaconVisit();

    $this->postJson(route('capell-campaigns.conversions'), [
        'type' => 'cta_click',
        'url' => 'https://example.test/spring-b',
        'goal_key' => 'book-demo',
        'cta_key' => 'hero-cta',
        'visit_id' => $visit->uuid,
    ])->assertNoContent();

    $conversion = CampaignConversion::query()->firstOrFail();

    expect(CampaignConversion::query()->count())->toBe(1);
    expect($conversion->campaign_conversion_goal_id)->toBe($secondGoal->getKey());
    expect($conversion->campaign_conversion_goal_id)->not->toBe($firstGoal->getKey());
    expect($conversion->campaign_landing_page_id)->toBe($secondLandingPage->getKey());
    expect($conversion->source_id)->toBe($secondCtaWidget->getKey());
});

it('does not record cta click beacon conversions when the url cannot resolve a campaign landing page', function (): void {
    $firstCampaign = CampaignGroup::factory()->create();
    $secondCampaign = CampaignGroup::factory()->create();
    CampaignConversionGoal::factory()
        ->for($firstCampaign, 'campaignGroup')
        ->create([
            'key' => 'book-demo',
            'type' => ConversionGoalType::CtaClick,
        ]);
    CampaignConversionGoal::factory()
        ->for($secondCampaign, 'campaignGroup')
        ->create([
            'key' => 'book-demo',
            'type' => ConversionGoalType::CtaClick,
        ]);

    $this->postJson(route('capell-campaigns.conversions'), [
        'type' => 'cta_click',
        'url' => 'https://example.test/no-campaign-page',
        'goal_key' => 'book-demo',
    ])->assertNoContent();

    expect(CampaignConversion::query()->count())->toBe(0);
});

it('rejects campaign conversion beacons from invalid origins', function (): void {
    $this
        ->withHeader('Origin', 'https://evil.example')
        ->postJson(route('capell-campaigns.conversions'), [
            'type' => 'page_view',
            'url' => 'https://example.test/demo',
        ])
        ->assertForbidden();

    expect(CampaignConversion::query()->count())->toBe(0);
});

it('uses the translated path length validation message', function (): void {
    $this->postJson(route('capell-campaigns.conversions'), [
        'type' => 'page_view',
        'url' => 'https://example.test/' . str_repeat('a', 513),
    ])
        ->assertUnprocessable()
        ->assertJsonPath(
            'errors.url.1',
            __('capell-campaign-studio::generic.validation.url_path_max', [
                'attribute' => 'url',
                'max' => 512,
            ]),
        );
});

function campaignBeaconVisit(): InsightsVisit
{
    return InsightsVisit::factory()->create([
        'consent_region' => InsightsConsentRegion::OutsideUkOrEurope,
    ]);
}
