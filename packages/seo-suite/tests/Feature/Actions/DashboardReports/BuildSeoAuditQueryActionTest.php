<?php

declare(strict_types=1);

use Capell\Core\Database\Factories\LanguageFactory;
use Capell\Core\Database\Factories\PageFactory;
use Capell\Core\Database\Factories\SiteFactory;
use Capell\SeoSuite\Actions\DashboardReports\BuildSeoAuditQueryAction;
use Capell\SeoSuite\Filament\Pages\Tables\SeoAuditTable;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

uses(CreatesAdminUser::class);

beforeEach(function (): void {
    test()->actingAsAdmin();
});

it('includes healthy and unhealthy pages in the site wide seo audit query', function (): void {
    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()->recycle($language)->language($language)->withTranslations($language)->create();
    $healthyPage = PageFactory::new()
        ->site($site)
        ->withTranslations($language, [
            'meta' => [
                'title' => 'A healthy search title for this content page',
                'description' => 'A healthy search description that gives search engines a useful summary.',
            ],
        ])
        ->create();
    $unhealthyPage = PageFactory::new()
        ->site($site)
        ->withTranslations($language, ['meta' => []])
        ->create();

    $pageIds = BuildSeoAuditQueryAction::run()->pluck('id')->all();

    expect($pageIds)
        ->toContain($unhealthyPage->getKey())
        ->toContain($healthyPage->getKey());
});

it('exposes snapshot backed seo audit filters', function (): void {
    $reflectionClass = new ReflectionClass(SeoAuditTable::class);
    $reflectionMethod = $reflectionClass->getMethod('getTableFilters');
    $filters = capell_test_collect($reflectionMethod->invoke(null));

    expect($filters->map(fn (mixed $filter): string => $filter->getName())->all())->toContain(
        'severity',
        'issue_key',
        'score_band',
        'schema_status',
        'robots_status',
        'canonical_status',
        'search_console_status',
        'snapshot_state',
    );
});

it('uses the site language for snapshot backed audit columns', function (): void {
    $english = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $french = LanguageFactory::new()->create(['name' => 'French', 'code' => 'fr']);
    $site = SiteFactory::new()
        ->recycle($english)
        ->language($english)
        ->withTranslations([$english, $french])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations([
            $english,
            $french,
        ], [
            $english->id => [
                'meta' => [
                    'title' => 'A healthy English search title',
                    'description' => 'A healthy English search description for this content page.',
                ],
            ],
            $french->id => [
                'meta' => [
                    'title' => '',
                    'description' => '',
                ],
            ],
        ])
        ->create();

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $english->getKey(),
        'score' => 100,
        'critical_count' => 0,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 1,
        'schema_status' => 'passed',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $french->getKey(),
        'score' => 50,
        'critical_count' => 2,
        'warning_count' => 1,
        'notice_count' => 0,
        'passed_count' => 0,
        'schema_status' => 'missing',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    $auditedPage = BuildSeoAuditQueryAction::run()
        ->whereKey($page->getKey())
        ->firstOrFail();

    $reflectionMethod = new ReflectionMethod(SeoAuditTable::class, 'snapshotFor');

    $snapshot = $reflectionMethod->invoke(null, $auditedPage);
    throw_unless($snapshot instanceof PageSeoSnapshot, RuntimeException::class);

    expect($snapshot)->toBeInstanceOf(PageSeoSnapshot::class)
        ->and($snapshot->language_id)->toBe($english->getKey())
        ->and($snapshot->critical_count)->toBe(0);
});

it('uses eager loaded snapshots for seo audit table state', function (): void {
    (new ReflectionProperty(SeoAuditTable::class, 'snapshots'))->setValue(null, []);

    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()
        ->recycle($language)
        ->language($language)
        ->withTranslations($language)
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language)
        ->create();

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 88,
        'critical_count' => 0,
        'warning_count' => 1,
        'notice_count' => 0,
        'passed_count' => 2,
        'schema_status' => 'warning',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    $auditedPage = BuildSeoAuditQueryAction::run()
        ->whereKey($page->getKey())
        ->firstOrFail();
    $reflectionMethod = new ReflectionMethod(SeoAuditTable::class, 'snapshotFor');
    $queries = [];

    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $snapshot = $reflectionMethod->invoke(null, $auditedPage);
    throw_unless($snapshot instanceof PageSeoSnapshot, RuntimeException::class);

    expect($snapshot)->toBeInstanceOf(PageSeoSnapshot::class)
        ->and($snapshot->score)->toBe(88)
        ->and(collect($queries)->filter(fn (string $query): bool => str_contains($query, 'page_seo_snapshots')))->toBeEmpty();
});

it('keeps seo audit table snapshot cache scoped to the current request', function (): void {
    (new ReflectionProperty(SeoAuditTable::class, 'snapshots'))->setValue(null, []);

    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()
        ->recycle($language)
        ->language($language)
        ->withTranslations($language)
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language)
        ->create();

    $snapshot = PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 20,
        'critical_count' => 1,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 2,
        'schema_status' => 'warning',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    $reflectionMethod = new ReflectionMethod(SeoAuditTable::class, 'snapshotFor');
    app()->instance('request', Request::create('/seo-audit/first'));

    $firstRequestPage = BuildSeoAuditQueryAction::run()
        ->whereKey($page->getKey())
        ->firstOrFail();
    $firstSnapshot = $reflectionMethod->invoke(null, $firstRequestPage);

    $snapshot->forceFill(['score' => 91])->save();
    app()->instance('request', Request::create('/seo-audit/second'));

    $secondRequestPage = BuildSeoAuditQueryAction::run()
        ->whereKey($page->getKey())
        ->firstOrFail();
    $secondSnapshot = $reflectionMethod->invoke(null, $secondRequestPage);
    throw_unless($firstSnapshot instanceof PageSeoSnapshot, RuntimeException::class);
    throw_unless($secondSnapshot instanceof PageSeoSnapshot, RuntimeException::class);

    expect($firstSnapshot->score)->toBe(20)
        ->and($secondSnapshot->score)->toBe(91);
});

it('does not query per row when eager loaded seo snapshots are empty', function (): void {
    (new ReflectionProperty(SeoAuditTable::class, 'snapshots'))->setValue(null, []);

    $language = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $site = SiteFactory::new()
        ->recycle($language)
        ->language($language)
        ->withTranslations($language)
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations($language)
        ->create();

    $auditedPage = BuildSeoAuditQueryAction::run()
        ->whereKey($page->getKey())
        ->firstOrFail();
    $reflectionMethod = new ReflectionMethod(SeoAuditTable::class, 'snapshotFor');
    $queries = [];

    DB::listen(static function (QueryExecuted $query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $snapshot = $reflectionMethod->invoke(null, $auditedPage);

    expect($snapshot)->toBeNull()
        ->and(collect($queries)->filter(fn (string $query): bool => str_contains($query, 'page_seo_snapshots')))->toBeEmpty();
});

it('constrains severity filters to the displayed snapshot language', function (): void {
    $english = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $french = LanguageFactory::new()->create(['name' => 'French', 'code' => 'fr']);
    $site = SiteFactory::new()
        ->recycle($english)
        ->language($english)
        ->withTranslations([$english, $french])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations([$english, $french])
        ->create();

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $english->getKey(),
        'score' => 100,
        'critical_count' => 0,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 3,
        'issue_keys' => [],
        'schema_status' => 'passed',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $french->getKey(),
        'score' => 40,
        'critical_count' => 1,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 0,
        'issue_keys' => ['meta_title'],
        'schema_status' => 'missing',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    $reflectionMethod = new ReflectionMethod(SeoAuditTable::class, 'whereSeveritySnapshot');
    $criticalQuery = BuildSeoAuditQueryAction::run()->whereKey($page->getKey());
    $cleanQuery = BuildSeoAuditQueryAction::run()->whereKey($page->getKey());

    $reflectionMethod->invoke(null, $criticalQuery, 'critical');
    $reflectionMethod->invoke(null, $cleanQuery, 'clean');

    expect($criticalQuery->exists())->toBeFalse()
        ->and($cleanQuery->exists())->toBeTrue();
});

it('constrains issue key filters to the displayed snapshot language', function (): void {
    $english = LanguageFactory::new()->create(['name' => 'English', 'code' => 'en']);
    $french = LanguageFactory::new()->create(['name' => 'French', 'code' => 'fr']);
    $site = SiteFactory::new()
        ->recycle($english)
        ->language($english)
        ->withTranslations([$english, $french])
        ->create();
    $page = PageFactory::new()
        ->site($site)
        ->withTranslations([$english, $french])
        ->create();

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $english->getKey(),
        'score' => 100,
        'critical_count' => 0,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 3,
        'issue_keys' => [],
        'schema_status' => 'passed',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $french->getKey(),
        'score' => 40,
        'critical_count' => 1,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 0,
        'issue_keys' => ['meta_title'],
        'schema_status' => 'missing',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    $reflectionMethod = new ReflectionMethod(SeoAuditTable::class, 'whereIssueKeySnapshot');
    $issueKeyQuery = BuildSeoAuditQueryAction::run()->whereKey($page->getKey());

    $reflectionMethod->invoke(null, $issueKeyQuery, 'meta_title');

    expect($issueKeyQuery->exists())->toBeFalse();
});
