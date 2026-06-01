<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\LanguageFactory;
use Capell\Core\Database\Factories\PageFactory;
use Capell\Core\Database\Factories\SiteFactory;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\BuildSeoIntelligenceOpportunitiesAction;
use Capell\SeoSuite\Actions\Dashboard\BuildSeoIntelligenceRowsAction;
use Capell\SeoSuite\Actions\NormalizeTargetKeywordsAction;
use Capell\SeoSuite\Actions\PersistSearchConsoleQueryMetricAction;
use Capell\SeoSuite\Data\SeoOpportunityRowData;
use Capell\SeoSuite\Enums\SeoOpportunityTypeEnum;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Collection;

function createSeoIntelligenceGlobalUser(): Authenticatable
{
    return new class extends Authenticatable implements FilamentUser
    {
        /** @use HasFactory<Factory<static>> */
        use HasFactory;

        protected $table = 'users';

        public function canAccessPanel(Panel $panel): bool
        {
            return true;
        }

        public function isGlobalAdmin(): bool
        {
            return true;
        }

        /** @return Collection<int, int> */
        public function getAssignedSiteIds(): Collection
        {
            return collect();
        }
    };
}

it('normalizes comma and newline separated target keywords', function (): void {
    expect(NormalizeTargetKeywordsAction::run(" Capell CMS, SEO Suite\ncapell cms\r\n Search Console "))
        ->toBe(['capell cms', 'seo suite', 'search console'])
        ->and(NormalizeTargetKeywordsAction::run([' Local SEO ', 'local seo', '', false]))
        ->toBe(['local seo']);
});

it('classifies quick win, ctr, declining, and cannibalization opportunities', function (): void {
    $site = Site::factory()->create();
    test()->actingAs(createSeoIntelligenceGlobalUser());
    $windowStart = now()->subDays(28);
    $windowEnd = now();

    PersistSearchConsoleQueryMetricAction::run(
        siteId: (int) $site->getKey(),
        query: 'capell cms',
        url: 'https://example.com/about',
        windowStart: $windowStart,
        windowEnd: $windowEnd,
        clicks: 10,
        impressions: 600,
        ctr: 0.01,
        averagePosition: 8.2,
        previousClicks: 30,
        previousImpressions: 900,
        previousCtr: 0.03,
        previousAveragePosition: 5.1,
    );
    PersistSearchConsoleQueryMetricAction::run(
        siteId: (int) $site->getKey(),
        query: 'capell cms',
        url: 'https://example.com/services',
        windowStart: $windowStart,
        windowEnd: $windowEnd,
        clicks: 2,
        impressions: 180,
        ctr: 0.01,
        averagePosition: 12.4,
        previousClicks: 1,
        previousImpressions: 120,
        previousCtr: 0.01,
        previousAveragePosition: 13.0,
    );

    $types = BuildSeoIntelligenceOpportunitiesAction::run(10)
        ->map(fn (SeoOpportunityRowData $row): SeoOpportunityTypeEnum => $row->type)
        ->all();

    expect($types)->toContain(SeoOpportunityTypeEnum::QuickWin)
        ->and($types)->toContain(SeoOpportunityTypeEnum::CtrOpportunity)
        ->and($types)->toContain(SeoOpportunityTypeEnum::Declining)
        ->and($types)->toContain(SeoOpportunityTypeEnum::Cannibalization);
});

it('maps seo intelligence opportunities into dashboard rows', function (): void {
    $site = Site::factory()->create();
    test()->actingAs(createSeoIntelligenceGlobalUser());

    PersistSearchConsoleQueryMetricAction::run(
        siteId: (int) $site->getKey(),
        query: 'capell cms',
        url: 'https://example.com/about',
        windowStart: now()->subDays(28),
        windowEnd: now(),
        clicks: 12,
        impressions: 1000,
        ctr: 0.0123,
        averagePosition: 8.24,
        previousClicks: 20,
        previousImpressions: 900,
        previousCtr: 0.02,
        previousAveragePosition: 5.0,
    );

    $row = BuildSeoIntelligenceRowsAction::run(1)->first();

    throw_if($row === null, RuntimeException::class, 'Expected SEO intelligence row to be built.');

    expect($row['id'])->toStartWith('seo-intelligence-0-')
        ->and($row['type'])->toBe(SeoOpportunityTypeEnum::QuickWin->getLabel())
        ->and($row['query'])->toBe('capell cms')
        ->and($row['url'])->toBe('https://example.com/about')
        ->and($row['priority'])->toBe(82)
        ->and($row['impressions'])->toBe(1000)
        ->and($row['clicks'])->toBe(12)
        ->and($row['ctr'])->toBe(1.2)
        ->and($row['average_position'])->toBe('8.2');
});

it('detects target keywords with no search console visibility', function (): void {
    $language = LanguageFactory::new()->create();
    $site = SiteFactory::new()
        ->recycle($language)
        ->language($language)
        ->withTranslations($language, siteDomainData: ['scheme' => 'https', 'domain' => 'example.com', 'path' => null])
        ->create();
    PageFactory::new()
        ->site($site)
        ->withTranslations($language, data: ['meta' => ['keywords' => "Primary Term\nSecondary Term"]])
        ->create();
    test()->actingAs(createSeoIntelligenceGlobalUser());

    $opportunity = BuildSeoIntelligenceOpportunitiesAction::run(10)
        ->first(fn (SeoOpportunityRowData $row): bool => $row->type === SeoOpportunityTypeEnum::MissingTargetVisibility);

    throw_unless($opportunity instanceof SeoOpportunityRowData, RuntimeException::class, 'Expected missing target visibility opportunity.');

    expect($opportunity->query)->toBe('primary term');
});

it('matches target keywords to the page url language', function (): void {
    $english = LanguageFactory::new()->english()->create();
    $french = LanguageFactory::new()->french()->create();
    $site = SiteFactory::new()
        ->recycle($english)
        ->language($english)
        ->withTranslations([$english, $french], siteDomainData: ['scheme' => 'https', 'domain' => 'example.com', 'path' => null])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations([$english, $french], data: [
            $english->getKey() => ['meta' => ['keywords' => 'English Target']],
            $french->getKey() => ['meta' => ['keywords' => 'French Target']],
        ])
        ->create();

    $page->pageUrls()->where('language_id', $english->getKey())->update(['url' => '/english']);
    $page->pageUrls()->where('language_id', $french->getKey())->update(['url' => '/french']);

    test()->actingAs(createSeoIntelligenceGlobalUser());

    $queries = BuildSeoIntelligenceOpportunitiesAction::run(10, 'https://example.com/english')
        ->pluck('query')
        ->all();

    expect($queries)->toContain('english target')
        ->and($queries)->not->toContain('french target');
});

it('scopes technical drift to the target site and language', function (): void {
    $english = LanguageFactory::new()->english()->create();
    $french = LanguageFactory::new()->french()->create();
    $site = SiteFactory::new()
        ->recycle($english)
        ->language($english)
        ->withTranslations([$english, $french], siteDomainData: ['scheme' => 'https', 'domain' => 'example.com', 'path' => null])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations([$english, $french], data: [
            $english->getKey() => ['meta' => ['keywords' => 'English Target']],
            $french->getKey() => ['meta' => ['keywords' => 'French Target']],
        ])
        ->create();

    $page->pageUrls()->where('language_id', $english->getKey())->update(['url' => '/english']);
    $page->pageUrls()->where('language_id', $french->getKey())->update(['url' => '/french']);

    PersistSearchConsoleQueryMetricAction::run(
        siteId: (int) $site->getKey(),
        query: 'English Target',
        url: 'https://example.com/english',
        windowStart: now()->subDays(28),
        windowEnd: now(),
        clicks: 4,
        impressions: 40,
        ctr: 0.10,
        averagePosition: 6.0,
        previousClicks: 2,
        previousImpressions: 30,
        previousCtr: 0.07,
        previousAveragePosition: 7.0,
    );

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $french->getKey(),
        'score' => 70,
        'robots_status' => 'warning',
        'canonical_status' => 'passed',
        'computed_at' => now(),
    ]);

    test()->actingAs(createSeoIntelligenceGlobalUser());

    $englishRows = BuildSeoIntelligenceOpportunitiesAction::run(10, 'https://example.com/english');

    expect($englishRows->pluck('type')->all())->not->toContain(SeoOpportunityTypeEnum::TechnicalDrift);

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $english->getKey(),
        'score' => 70,
        'robots_status' => 'warning',
        'canonical_status' => 'passed',
        'computed_at' => now(),
    ]);

    $englishRows = BuildSeoIntelligenceOpportunitiesAction::run(10, 'https://example.com/english');

    expect($englishRows->pluck('type')->all())->toContain(SeoOpportunityTypeEnum::TechnicalDrift);
});
