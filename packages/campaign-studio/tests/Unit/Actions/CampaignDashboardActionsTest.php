<?php

declare(strict_types=1);

use Capell\CampaignStudio\Actions\ApplyCampaignPageDefaultsAction;
use Capell\CampaignStudio\Actions\BuildCampaignConversionFunnelAction;
use Capell\CampaignStudio\Actions\BuildCampaignOverviewStatsAction;
use Capell\CampaignStudio\Actions\BuildTopCampaignStudioQueryAction;
use Capell\CampaignStudio\Actions\BuildTopLandingPagesQueryAction;
use Capell\CampaignStudio\Actions\RecordCampaignConversionAction;
use Capell\CampaignStudio\Actions\RecordCtaClickConversionAction;
use Capell\CampaignStudio\Actions\RecordPageViewConversionAction;
use Capell\CampaignStudio\Actions\ResolveCampaignFromUrlAction;
use Capell\CampaignStudio\Enums\CampaignStatus;
use Capell\CampaignStudio\Enums\ConversionGoalType;
use Capell\CampaignStudio\Models\CampaignConversion;
use Capell\CampaignStudio\Models\CampaignConversionGoal;
use Capell\CampaignStudio\Models\CampaignGroup;
use Capell\CampaignStudio\Models\CampaignLandingPage;
use Capell\Core\Models\Layout;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Insights\Models\InsightsVisit;
use Carbon\CarbonImmutable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection as SupportCollection;

it('resolves active campaigns from utm campaign values before falling back to landing page urls', function (): void {
    $utmCampaign = CampaignGroup::factory()->create([
        'name' => 'Spring Launch',
        'slug' => 'spring-launch-slug',
        'utm_campaign' => 'spring-launch',
    ]);
    CampaignGroup::factory()->create([
        'slug' => 'retired-campaign',
        'status' => CampaignStatus::Paused,
    ]);
    $fallbackCampaign = CampaignGroup::factory()->create([
        'name' => 'Fallback Campaign',
    ]);
    $page = Page::factory()->create();

    PageUrl::factory()
        ->page($page)
        ->create([
            'site_id' => $page->site_id,
            'url' => '/campaign/fallback',
        ]);

    CampaignLandingPage::factory()
        ->for($fallbackCampaign, 'campaignGroup')
        ->create([
            'page_id' => $page->getKey(),
        ]);

    expect(ResolveCampaignFromUrlAction::run('https://capell.test/anything?utm_campaign=spring-launch'))
        ->is($utmCampaign)->toBeTrue()
        ->and(ResolveCampaignFromUrlAction::run('https://capell.test/anything?utm_campaign=retired-campaign'))->toBeNull()
        ->and(ResolveCampaignFromUrlAction::run('https://capell.test/campaign/fallback')->is($fallbackCampaign))->toBeTrue();
});

it('builds campaign overview stats from active campaigns, conversions, and campaign visits in the requested window', function (): void {
    $startsAt = CarbonImmutable::parse('2026-04-01 00:00:00');
    $endsAt = CarbonImmutable::parse('2026-04-30 23:59:59');
    $campaign = CampaignGroup::factory()->create([
        'utm_campaign' => 'spring-launch',
    ]);
    CampaignGroup::factory()->create([
        'status' => CampaignStatus::Paused,
        'utm_campaign' => 'paused-launch',
    ]);
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create();

    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-15 12:00:00')]);
    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-05-02 12:00:00')]);

    InsightsVisit::factory()->create([
        'utm_campaign' => 'spring-launch',
        'last_seen_at' => CarbonImmutable::parse('2026-04-10 09:00:00'),
    ]);
    InsightsVisit::factory()->create([
        'utm_campaign' => 'spring-launch',
        'last_seen_at' => CarbonImmutable::parse('2026-04-11 09:00:00'),
    ]);
    InsightsVisit::factory()->create([
        'utm_campaign' => 'spring-launch',
        'last_seen_at' => CarbonImmutable::parse('2026-05-01 09:00:00'),
    ]);

    $stats = BuildCampaignOverviewStatsAction::run($startsAt, $endsAt);

    expect($stats)->toBe([
        'active_campaign-studio' => 1,
        'conversions' => 1,
        'conversion_rate' => 50.0,
    ]);
});

it('excludes campaigns with a blank utm_campaign from the overview visit count', function (): void {
    $startsAt = CarbonImmutable::parse('2026-04-01 00:00:00');
    $endsAt = CarbonImmutable::parse('2026-04-30 23:59:59');
    $campaign = CampaignGroup::factory()->create([
        'utm_campaign' => null,
    ]);
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create();

    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-15 12:00:00')]);

    // Unrelated visit with a null utm_campaign: it must not join to the campaign
    // group with a null utm_campaign and inflate the visit denominator.
    InsightsVisit::factory()->create([
        'utm_campaign' => null,
        'last_seen_at' => CarbonImmutable::parse('2026-04-10 09:00:00'),
    ]);

    $stats = BuildCampaignOverviewStatsAction::run($startsAt, $endsAt);

    expect($stats)->toBe([
        'active_campaign-studio' => 1,
        'conversions' => 1,
        'conversion_rate' => 0.0,
    ]);
});

it('counts duplicate campaign utm visits once in overview conversion rates', function (): void {
    $startsAt = CarbonImmutable::parse('2026-04-01 00:00:00');
    $endsAt = CarbonImmutable::parse('2026-04-30 23:59:59');
    $campaign = CampaignGroup::factory()->create([
        'utm_campaign' => 'shared-launch',
    ]);
    CampaignGroup::factory()->create([
        'utm_campaign' => 'shared-launch',
    ]);
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create();

    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-15 12:00:00')]);

    InsightsVisit::factory()->create([
        'utm_campaign' => 'shared-launch',
        'last_seen_at' => CarbonImmutable::parse('2026-04-10 09:00:00'),
    ]);
    InsightsVisit::factory()->create([
        'utm_campaign' => 'shared-launch',
        'last_seen_at' => CarbonImmutable::parse('2026-04-11 09:00:00'),
    ]);

    $stats = BuildCampaignOverviewStatsAction::run($startsAt, $endsAt);

    expect($stats)->toBe([
        'active_campaign-studio' => 2,
        'conversions' => 1,
        'conversion_rate' => 50.0,
    ]);
});

it('scopes campaign dashboard actions to the actor assigned sites', function (): void {
    $startsAt = CarbonImmutable::parse('2026-04-01 00:00:00');
    $endsAt = CarbonImmutable::parse('2026-04-30 23:59:59');
    $assignedSite = Site::factory()->create();
    $hiddenSite = Site::factory()->create();
    $assignedCampaign = CampaignGroup::factory()->create([
        'site_id' => $assignedSite->getKey(),
        'name' => 'Assigned Campaign',
        'utm_campaign' => 'assigned-campaign',
    ]);
    $hiddenCampaign = CampaignGroup::factory()->create([
        'site_id' => $hiddenSite->getKey(),
        'name' => 'Hidden Campaign',
        'utm_campaign' => 'hidden-campaign',
    ]);
    $assignedGoal = CampaignConversionGoal::factory()->for($assignedCampaign, 'campaignGroup')->create([
        'site_id' => $assignedSite->getKey(),
    ]);
    $hiddenGoal = CampaignConversionGoal::factory()->for($hiddenCampaign, 'campaignGroup')->create([
        'site_id' => $hiddenSite->getKey(),
    ]);
    $assignedLandingPage = CampaignLandingPage::factory()->for($assignedCampaign, 'campaignGroup')->create([
        'headline' => 'Assigned Landing',
    ]);
    CampaignLandingPage::factory()->for($hiddenCampaign, 'campaignGroup')->create([
        'headline' => 'Hidden Landing',
    ]);

    CampaignConversion::factory()
        ->for($assignedCampaign, 'campaignGroup')
        ->for($assignedGoal, 'goal')
        ->for($assignedLandingPage, 'landingPage')
        ->create([
            'site_id' => $assignedSite->getKey(),
            'converted_at' => CarbonImmutable::parse('2026-04-12 12:00:00'),
        ]);
    CampaignConversion::factory()
        ->for($hiddenCampaign, 'campaignGroup')
        ->for($hiddenGoal, 'goal')
        ->create([
            'site_id' => $hiddenSite->getKey(),
            'converted_at' => CarbonImmutable::parse('2026-04-12 12:00:00'),
        ]);
    InsightsVisit::factory()->create([
        'utm_campaign' => 'assigned-campaign',
        'last_seen_at' => CarbonImmutable::parse('2026-04-09 10:00:00'),
    ]);
    InsightsVisit::factory()->create([
        'utm_campaign' => 'hidden-campaign',
        'last_seen_at' => CarbonImmutable::parse('2026-04-09 10:00:00'),
    ]);

    auth()->setUser(campaignDashboardScopedUser(collect([(int) $assignedSite->getKey()])));

    $stats = BuildCampaignOverviewStatsAction::run($startsAt, $endsAt);
    $topCampaigns = BuildTopCampaignStudioQueryAction::run(limit: 5, startsAt: $startsAt, endsAt: $endsAt);
    $topLandingPages = BuildTopLandingPagesQueryAction::run(limit: 5, startsAt: $startsAt, endsAt: $endsAt);

    expect($stats)->toBe([
        'active_campaign-studio' => 1,
        'conversions' => 1,
        'conversion_rate' => 100.0,
    ])
        ->and($topCampaigns)->toHaveCount(1)
        ->and($topCampaigns[0]->campaignName)->toBe('Assigned Campaign')
        ->and($topLandingPages)->toHaveCount(1)
        ->and($topLandingPages[0]->landingPageName)->toBe('Assigned Landing');
});

it('ranks top campaigns by in-window conversions and calculates visit conversion rates', function (): void {
    $startsAt = CarbonImmutable::parse('2026-04-01 00:00:00');
    $endsAt = CarbonImmutable::parse('2026-04-30 23:59:59');
    $firstCampaign = CampaignGroup::factory()->create([
        'name' => 'Alpha Campaign',
        'utm_campaign' => 'alpha-campaign',
    ]);
    $secondCampaign = CampaignGroup::factory()->create([
        'name' => 'Beta Campaign',
        'utm_campaign' => 'beta-campaign',
    ]);
    $firstGoal = CampaignConversionGoal::factory()->for($firstCampaign, 'campaignGroup')->create();
    $secondGoal = CampaignConversionGoal::factory()->for($secondCampaign, 'campaignGroup')->create();

    CampaignConversion::factory()
        ->count(2)
        ->for($firstCampaign, 'campaignGroup')
        ->for($firstGoal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-08 12:00:00')]);
    CampaignConversion::factory()
        ->for($secondCampaign, 'campaignGroup')
        ->for($secondGoal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-08 12:00:00')]);
    CampaignConversion::factory()
        ->for($firstCampaign, 'campaignGroup')
        ->for($firstGoal, 'goal')
        ->create(['converted_at' => CarbonImmutable::parse('2026-05-08 12:00:00')]);

    InsightsVisit::factory()->count(4)->create([
        'utm_campaign' => 'alpha-campaign',
        'last_seen_at' => CarbonImmutable::parse('2026-04-09 10:00:00'),
    ]);
    InsightsVisit::factory()->count(2)->create([
        'utm_campaign' => 'beta-campaign',
        'last_seen_at' => CarbonImmutable::parse('2026-04-09 10:00:00'),
    ]);

    $rows = BuildTopCampaignStudioQueryAction::run(limit: 2, startsAt: $startsAt, endsAt: $endsAt);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]->campaignGroupId)->toBe($firstCampaign->getKey())
        ->and($rows[0]->conversions)->toBe(2)
        ->and($rows[0]->visits)->toBe(4)
        ->and($rows[0]->conversionRate)->toBe(50.0)
        ->and($rows[1]->campaignGroupId)->toBe($secondCampaign->getKey())
        ->and($rows[1]->conversions)->toBe(1);
});

it('ranks landing pages by in-window conversions with their campaign names', function (): void {
    $startsAt = CarbonImmutable::parse('2026-04-01 00:00:00');
    $endsAt = CarbonImmutable::parse('2026-04-30 23:59:59');
    $campaign = CampaignGroup::factory()->create([
        'name' => 'Spring Launch',
    ]);
    $goal = CampaignConversionGoal::factory()->for($campaign, 'campaignGroup')->create();
    $firstLandingPage = CampaignLandingPage::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['headline' => 'Signup Page']);
    $secondLandingPage = CampaignLandingPage::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['headline' => 'Demo Page']);

    CampaignConversion::factory()
        ->count(2)
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->for($firstLandingPage, 'landingPage')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-12 12:00:00')]);
    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->for($secondLandingPage, 'landingPage')
        ->create(['converted_at' => CarbonImmutable::parse('2026-04-12 12:00:00')]);
    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($goal, 'goal')
        ->for($firstLandingPage, 'landingPage')
        ->create(['converted_at' => CarbonImmutable::parse('2026-05-12 12:00:00')]);

    $rows = BuildTopLandingPagesQueryAction::run(limit: 2, startsAt: $startsAt, endsAt: $endsAt);

    expect($rows)->toHaveCount(2)
        ->and($rows[0]->landingPageId)->toBe($firstLandingPage->getKey())
        ->and($rows[0]->landingPageName)->toBe('Signup Page')
        ->and($rows[0]->campaignName)->toBe('Spring Launch')
        ->and($rows[0]->conversions)->toBe(2)
        ->and($rows[1]->landingPageId)->toBe($secondLandingPage->getKey())
        ->and($rows[1]->conversions)->toBe(1);
});

it('builds a conversion funnel ordered by goal conversion volume', function (): void {
    $campaign = CampaignGroup::factory()->create();
    $firstGoal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['name' => 'Lead form']);
    $secondGoal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['name' => 'Book demo']);

    CampaignConversion::factory()
        ->count(2)
        ->for($campaign, 'campaignGroup')
        ->for($secondGoal, 'goal')
        ->create();
    CampaignConversion::factory()
        ->for($campaign, 'campaignGroup')
        ->for($firstGoal, 'goal')
        ->create();

    $funnel = BuildCampaignConversionFunnelAction::run($campaign);

    expect($funnel->all())->toBe([
        ['goal' => 'Book demo', 'conversions' => 2],
        ['goal' => 'Lead form', 'conversions' => 1],
    ]);
});

it('drops stale visit attribution outside the configured conversion lookback window', function (): void {
    config()->set('capell-campaign-studio.attribution.lookback_days', 7);
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-04-30 12:00:00'));

    try {
        $campaign = CampaignGroup::factory()->create();
        $goal = CampaignConversionGoal::factory()
            ->for($campaign, 'campaignGroup')
            ->create();
        $visit = InsightsVisit::factory()->create([
            'utm_campaign' => 'expired-campaign',
            'last_seen_at' => CarbonImmutable::parse('2026-04-20 12:00:00'),
        ]);

        $conversion = RecordCampaignConversionAction::run($goal, $visit);

        expect($conversion)->toBeInstanceOf(CampaignConversion::class)
            ->and($conversion->insights_visit_id)->toBeNull()
            ->and($conversion->attribution?->utmCampaign)->toBeNull();
    } finally {
        CarbonImmutable::setTestNow();
    }
});

it('records cta click conversions only for active cta click goals', function (): void {
    $campaign = CampaignGroup::factory()->create();
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create([
            'key' => 'primary-cta',
            'type' => ConversionGoalType::CtaClick,
            'is_active' => true,
        ]);
    CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create([
            'key' => 'inactive-cta',
            'type' => ConversionGoalType::CtaClick,
            'is_active' => false,
        ]);

    $conversion = RecordCtaClickConversionAction::run('primary-cta');

    expect($conversion)->toBeInstanceOf(CampaignConversion::class)
        ->and($conversion->campaign_conversion_goal_id)->toBe($goal->getKey())
        ->and(RecordCtaClickConversionAction::run('inactive-cta'))->toBeNull()
        ->and(RecordCtaClickConversionAction::run('missing-cta'))->toBeNull();
});

it('records page view conversions from landing pages with page view primary goals', function (): void {
    $campaign = CampaignGroup::factory()->create();
    $goal = CampaignConversionGoal::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['type' => ConversionGoalType::PageView]);
    $landingPage = CampaignLandingPage::factory()
        ->for($campaign, 'campaignGroup')
        ->create(['primary_goal_id' => $goal->getKey()]);
    $landingPageWithoutPageViewGoal = CampaignLandingPage::factory()
        ->for($campaign, 'campaignGroup')
        ->create();

    $conversion = RecordPageViewConversionAction::run($landingPage);

    expect($conversion)->toBeInstanceOf(CampaignConversion::class)
        ->and($conversion->campaign_conversion_goal_id)->toBe($goal->getKey())
        ->and($conversion->campaign_landing_page_id)->toBe($landingPage->getKey())
        ->and(RecordPageViewConversionAction::run($landingPageWithoutPageViewGoal))->toBeNull();
});

it('applies campaign defaults without replacing explicit page utm values', function (): void {
    $campaign = CampaignGroup::factory()->create([
        'slug' => 'fallback-campaign',
        'utm_source' => 'newsletter',
        'utm_medium' => 'email',
        'utm_campaign' => null,
    ]);

    $defaults = ApplyCampaignPageDefaultsAction::run(['utm_source' => 'paid'], $campaign);

    expect($defaults)->toBe([
        'utm_source' => 'paid',
        'utm_medium' => 'email',
        'utm_campaign' => 'fallback-campaign',
    ]);
});

it('installs campaign layouts through the console command', function (): void {
    $this->artisan('capell:campaign-studio-install-layouts')
        ->expectsOutputToContain('Campaign layouts installed. Created: 3, updated: 0, skipped: 0.')
        ->assertSuccessful();

    expect(Layout::query()->where('key', 'campaign-product-launch')->exists())->toBeTrue();
});

/**
 * @param  SupportCollection<int, int>  $assignedSiteIds
 */
function campaignDashboardScopedUser(SupportCollection $assignedSiteIds): Authenticatable
{
    $user = new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        public function canAccessPanel(Panel $panel): bool
        {
            return true;
        }

        /** @return SupportCollection<int, int> */
        public function getAssignedSiteIds(): SupportCollection
        {
            return $this->assignedSiteIds;
        }

        public function isGlobalAdmin(): bool
        {
            return false;
        }
    };
    $user->assignedSiteIds = $assignedSiteIds;

    return $user;
}
