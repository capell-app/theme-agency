<?php

declare(strict_types=1);

use Capell\Admin\Contracts\DashboardSettingsContributor;
use Capell\Admin\Enums\DashboardEnum;
use Capell\Admin\Facades\CapellAdmin;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\Dashboard\BuildAiDiscoveryCoverageStatsAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSearchConsoleDashboardStatsAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSearchConsolePageRowsAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSeoOpportunityRowsAction;
use Capell\SeoSuite\Filament\Settings\Contributors\SeoSuiteDashboardSettingsContributor;
use Capell\SeoSuite\Filament\Widgets\AiDiscoveryCoverageWidget;
use Capell\SeoSuite\Filament\Widgets\SearchConsoleOverviewWidget;
use Capell\SeoSuite\Filament\Widgets\SearchMovementWidget;
use Capell\SeoSuite\Filament\Widgets\SeoOpportunitiesWidget;
use Capell\SeoSuite\Filament\Widgets\TopSearchPagesWidget;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Capell\SeoSuite\Models\SearchConsoleUrlMetric;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Collection as SupportCollection;
use Livewire\Livewire;

/**
 * @param  Collection<array-key, mixed>  $assignedSiteIds
 */
function createSeoSuiteDashboardWidgetUser(SupportCollection $assignedSiteIds, bool $global = false): Authenticatable
{
    $user = new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        /** @var SupportCollection<int, int> */
        public SupportCollection $assignedSiteIds;

        public bool $global = false;

        protected $table = 'users';

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
            return $this->global;
        }
    };

    $user->forceFill([
        'name' => 'SEO Dashboard User',
        'email' => fake()->unique()->safeEmail(),
        'password' => bcrypt('password'),
    ]);
    $user->assignedSiteIds = $assignedSiteIds;
    $user->global = $global;

    return $user;
}

it('exposes SEO Suite dashboard settings keys with translated labels', function (): void {
    $entries = (new SeoSuiteDashboardSettingsContributor)->settingsKeys();

    expect(collect($entries)->pluck('key')->all())->toBe([
        'seo_search_console_overview',
        'seo_top_search_pages',
        'seo_search_movement',
        'seo_opportunities',
        'seo_ai_discovery_coverage',
    ]);

    foreach ($entries as $entry) {
        expect($entry['label'])->toBeString()->not->toBe('')
            ->and(str_contains($entry['label'], 'capell-seo-suite::'))->toBeFalse()
            ->and($entry['group'])->toBeString()->not->toBe('');
    }
});

it('registers SEO Suite dashboard widgets and settings contributor', function (): void {
    $contributors = collect(app()->tagged(DashboardSettingsContributor::TAG))
        ->map(fn (DashboardSettingsContributor $contributor): string => $contributor::class);

    expect($contributors)->toContain(SeoSuiteDashboardSettingsContributor::class)
        ->and(CapellAdmin::getDashboardWidgets(DashboardEnum::Main))
        ->toContain(SearchConsoleOverviewWidget::class)
        ->toContain(TopSearchPagesWidget::class)
        ->toContain(SearchMovementWidget::class)
        ->toContain(SeoOpportunitiesWidget::class)
        ->toContain(AiDiscoveryCoverageWidget::class);
});

it('renders SEO Suite dashboard widgets', function (string $widgetClass): void {
    Livewire::test($widgetClass)->assertOk();
})->with([
    SearchConsoleOverviewWidget::class,
    TopSearchPagesWidget::class,
    SearchMovementWidget::class,
    SeoOpportunitiesWidget::class,
    AiDiscoveryCoverageWidget::class,
]);

it('builds Search Console dashboard stats from each scoped site latest window', function (): void {
    $firstSite = Site::factory()->create();
    $secondSite = Site::factory()->create();
    $hiddenSite = Site::factory()->create();

    test()->actingAs(createSeoSuiteDashboardWidgetUser(collect([
        $firstSite->getKey(),
        $secondSite->getKey(),
    ])));

    SearchConsoleUrlMetric::query()->create([
        'site_id' => $firstSite->getKey(),
        'url' => 'https://example.com/old',
        'url_hash' => hash('sha256', 'https://example.com/old'),
        'window_start' => now()->subDays(60)->toDateString(),
        'window_end' => now()->subDays(32)->toDateString(),
        'clicks' => 999,
        'impressions' => 999,
        'ctr' => 1,
        'average_position' => 99,
        'previous_clicks' => 1,
        'previous_impressions' => 1,
        'click_delta' => 998,
    ]);
    SearchConsoleUrlMetric::query()->create([
        'site_id' => $firstSite->getKey(),
        'url' => 'https://example.com/a',
        'url_hash' => hash('sha256', 'https://example.com/a'),
        'window_start' => now()->subDays(28)->toDateString(),
        'window_end' => now()->toDateString(),
        'clicks' => 10,
        'impressions' => 100,
        'ctr' => 0.1,
        'average_position' => 2,
        'previous_clicks' => 5,
        'previous_impressions' => 50,
        'click_delta' => 5,
    ]);
    SearchConsoleUrlMetric::query()->create([
        'site_id' => $secondSite->getKey(),
        'url' => 'https://example.org/b',
        'url_hash' => hash('sha256', 'https://example.org/b'),
        'window_start' => now()->subDays(35)->toDateString(),
        'window_end' => now()->subDays(7)->toDateString(),
        'clicks' => 20,
        'impressions' => 300,
        'ctr' => 0.066,
        'average_position' => 8,
        'previous_clicks' => 30,
        'previous_impressions' => 300,
        'click_delta' => -10,
    ]);
    SearchConsoleUrlMetric::query()->create([
        'site_id' => $hiddenSite->getKey(),
        'url' => 'https://hidden.test/',
        'url_hash' => hash('sha256', 'https://hidden.test/'),
        'window_start' => now()->subDays(28)->toDateString(),
        'window_end' => now()->toDateString(),
        'clicks' => 500,
        'impressions' => 500,
        'ctr' => 1,
        'average_position' => 1,
        'previous_clicks' => 1,
        'previous_impressions' => 1,
        'click_delta' => 499,
    ]);

    $stats = BuildSearchConsoleDashboardStatsAction::run();
    $movementRows = BuildSearchConsolePageRowsAction::run('movement', 10);

    expect($stats->clicks)->toBe(30)
        ->and($stats->impressions)->toBe(400)
        ->and($stats->averagePosition)->toBe(6.5)
        ->and($stats->risingPages)->toBe(1)
        ->and($stats->decliningPages)->toBe(1)
        ->and($stats->mixedWindows)->toBeTrue()
        ->and($movementRows->pluck('url')->all())->toBe([
            'https://example.org/b',
            'https://example.com/a',
        ]);
});

it('builds SEO opportunities and AI Discovery coverage from scoped rows', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->language($language)->withTranslations($language)->create();
    $hiddenSite = Site::factory()->language($language)->withTranslations($language)->create();
    $page = Page::factory()->site($site)->withTranslations($language)->create();
    $hiddenPage = Page::factory()->site($hiddenSite)->withTranslations($language)->create();

    test()->actingAs(createSeoSuiteDashboardWidgetUser(collect([$site->getKey()])));

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 42,
        'critical_count' => 2,
        'warning_count' => 1,
        'notice_count' => 3,
        'passed_count' => 4,
    ]);
    PageSeoSnapshot::query()->create([
        'page_id' => $hiddenPage->getKey(),
        'site_id' => $hiddenSite->getKey(),
        'language_id' => $language->getKey(),
        'score' => 1,
        'critical_count' => 9,
        'warning_count' => 9,
        'notice_count' => 9,
        'passed_count' => 0,
    ]);
    AiDiscoveryPageProfile::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => true,
        'summary' => '',
        'section' => 'Pages',
        'priority' => 500,
    ]);
    AiDiscoveryPageProfile::query()->create([
        'page_id' => $hiddenPage->getKey(),
        'site_id' => $hiddenSite->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => false,
        'summary' => 'Hidden',
        'section' => 'Pages',
        'priority' => 500,
    ]);

    $opportunities = BuildSeoOpportunityRowsAction::run();
    $coverage = BuildAiDiscoveryCoverageStatsAction::run();
    $opportunity = $opportunities->first();

    expect($opportunities)->toHaveCount(1)
        ->and($opportunity['critical_count'] ?? null)->toBe(2)
        ->and($coverage)->toMatchArray([
            'included' => 1,
            'excluded' => 0,
            'missing_summary' => 1,
            'stale_markdown' => 1,
        ]);
});
