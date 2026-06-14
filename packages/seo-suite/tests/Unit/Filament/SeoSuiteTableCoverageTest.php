<?php

declare(strict_types=1);

use Capell\Core\Models\Blueprint;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Actions\ResolveAiDiscoveryProfileAction;
use Capell\SeoSuite\Actions\UpdateAiDiscoveryPageInclusionAction;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Capell\SeoSuite\Filament\Pages\SearchRankingsPage;
use Capell\SeoSuite\Filament\Pages\Tables\AiDiscoveryTable;
use Capell\SeoSuite\Filament\Pages\Tables\SeoAuditTable;
use Capell\SeoSuite\Filament\Pages\Tables\TranslationCoverageTable;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Capell\SeoSuite\Models\SearchConsoleQueryMetric;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;

it('exposes translation coverage table columns for page, language completeness, missing languages, and author', function (): void {
    $method = new ReflectionMethod(TranslationCoverageTable::class, 'configure');
    $columnsProperty = new ReflectionProperty(Table::class, 'columns');

    expect($method->isStatic())->toBeTrue()
        ->and($columnsProperty->isProtected())->toBeTrue();
});

it('calculates translation coverage and missing language names from loaded page relations', function (): void {
    $english = Language::factory()->create(['name' => 'English', 'code' => 'en']);
    $french = Language::factory()->create(['name' => 'French', 'code' => 'fr']);
    $german = Language::factory()->create(['name' => 'German', 'code' => 'de']);
    $site = Site::factory()
        ->language($english)
        ->withTranslations([$english, $french, $german])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->withTranslations([$english, $french])
        ->create();

    $coverageMethod = new ReflectionMethod(TranslationCoverageTable::class, 'calculateCoverage');
    $missingMethod = new ReflectionMethod(TranslationCoverageTable::class, 'getMissingLanguages');

    expect($coverageMethod->invoke(null, $page->fresh()))->toBe(67)
        ->and($missingMethod->invoke(null, $page->fresh()))->toBe(['German']);
});

it('returns zero translation coverage when a site has no configured languages', function (): void {
    $site = Site::factory()->create();
    $page = Page::factory()
        ->site($site)
        ->create();

    $coverageMethod = new ReflectionMethod(TranslationCoverageTable::class, 'calculateCoverage');

    expect($coverageMethod->invoke(null, $page->fresh()))->toBe(0);
});

it('exposes ai discovery table columns, filters, row actions, and bulk actions', function (): void {
    $columnsMethod = new ReflectionMethod(AiDiscoveryTable::class, 'getTableColumns');
    $filtersMethod = new ReflectionMethod(AiDiscoveryTable::class, 'getTableFilters');
    $actionsMethod = new ReflectionMethod(AiDiscoveryTable::class, 'getTableActions');
    $bulkActionsMethod = new ReflectionMethod(AiDiscoveryTable::class, 'getBulkActions');

    $columnNames = reflectedComponentNames($columnsMethod);
    $filterNames = reflectedComponentNames($filtersMethod);
    $actionNames = reflectedComponentNames($actionsMethod);
    $bulkActionNames = reflectedComponentNames($bulkActionsMethod);

    expect($columnNames)->toBe([
        'name',
        'site.name',
        'site.language.name',
        'ai_discovery_state',
        'ai_discovery_summary',
        'ai_discovery_readiness_issues',
        'ai_discovery_markdown',
        'updated_at',
    ])
        ->and($filterNames)->toBe(['site_id', 'include_in_ai_index', 'missing_ai_summary'])
        ->and($actionNames)->toBe([
            'edit_ai_discovery',
            'fill_ai_summary',
            'include_ai_index',
            'exclude_ai_index',
            'preview_markdown',
            'edit_page',
        ])
        ->and($bulkActionNames)->toBe(['include_ai_index', 'exclude_ai_index']);
});

it('configures and drives ai discovery table actions through profile workflows', function (): void {
    resetAiDiscoveryTableCaches();

    $language = Language::factory()->create(['name' => 'English', 'code' => 'en']);
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: [
            'domain' => 'example.test',
            'scheme' => 'https',
            'path' => null,
            'default' => true,
        ])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->type(Blueprint::factory()->page()->create(['status' => true]))
        ->withTranslations($language, [
            'title' => 'Discovery workflow page',
            'meta' => ['summary' => 'Workflow summary from translation metadata.'],
        ])
        ->create(['name' => 'Discovery workflow']);

    PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->state(['url' => '/discovery-workflow'])
        ->create();
    PageUrl::query()
        ->where('pageable_id', $page->getKey())
        ->where('pageable_type', $page->getMorphClass())
        ->update(['url' => '/discovery-workflow']);

    ResolveAiDiscoveryProfileAction::run($site, $language)->update([
        'default_include_pages' => false,
        'default_section' => 'Docs',
        'markdown_pages_enabled' => true,
    ]);

    $table = AiDiscoveryTable::configure(seoSuiteTableForCoverage());
    $actions = collect((new ReflectionMethod(AiDiscoveryTable::class, 'getTableActions'))->invoke(null))
        ->keyBy(fn (Action $action): string => $action->getName());
    $bulkActions = collect((new ReflectionMethod(AiDiscoveryTable::class, 'getBulkActions'))->invoke(null))
        ->keyBy(fn (BulkAction $action): string => $action->getName());

    $editAction = $actions->get('edit_ai_discovery');
    $includeAction = $actions->get('include_ai_index');
    $excludeAction = $actions->get('exclude_ai_index');
    $fillSummaryAction = $actions->get('fill_ai_summary');
    $bulkIncludeAction = $bulkActions->get('include_ai_index');
    $bulkExcludeAction = $bulkActions->get('exclude_ai_index');
    $query = $table->getQuery();

    throw_unless($query instanceof Builder, RuntimeException::class, 'Expected AI discovery table to expose an Eloquent query.');

    expect($query)->not->toBeNull()
        ->and($table->getDefaultSort($query, 'asc'))->toBe('updated_at')
        ->and($editAction)->toBeInstanceOf(Action::class)
        ->and($includeAction)->toBeInstanceOf(Action::class)
        ->and($excludeAction)->toBeInstanceOf(Action::class)
        ->and($fillSummaryAction)->toBeInstanceOf(Action::class)
        ->and($bulkIncludeAction)->toBeInstanceOf(BulkAction::class)
        ->and($bulkExcludeAction)->toBeInstanceOf(BulkAction::class);

    $editAction = seoSuiteTableAction($editAction);
    $includeAction = seoSuiteTableAction($includeAction);
    $excludeAction = seoSuiteTableAction($excludeAction);
    $fillSummaryAction = seoSuiteTableAction($fillSummaryAction);
    $bulkIncludeAction = seoSuiteBulkAction($bulkIncludeAction);
    $bulkExcludeAction = seoSuiteBulkAction($bulkExcludeAction);

    evaluateSeoSuiteTableAction($editAction, $page, [
        'include_in_ai_index' => true,
        'section' => 'Guides',
        'priority' => 250,
        'summary' => '',
        'markdown_override' => 'Manual markdown body',
        'exclude_reason' => null,
    ]);

    $profile = AiDiscoveryPageProfile::query()
        ->where('page_id', $page->getKey())
        ->where('site_id', $site->getKey())
        ->where('language_id', $language->getKey())
        ->firstOrFail();

    expect($profile->include_in_ai_index)->toBeTrue()
        ->and($profile->section)->toBe('Guides')
        ->and($profile->priority)->toBe(250)
        ->and($profile->markdown_override)->toBe('Manual markdown body');

    evaluateSeoSuiteTableAction($fillSummaryAction, $page->fresh());

    expect($profile->refresh()->summary)->toBe('Workflow summary from translation metadata.');

    evaluateSeoSuiteTableAction($excludeAction, $page->fresh());
    expect($profile->refresh()->include_in_ai_index)->toBeFalse();

    evaluateSeoSuiteTableAction($includeAction, $page->fresh());
    expect($profile->refresh()->include_in_ai_index)->toBeTrue();

    evaluateSeoSuiteBulkAction($bulkExcludeAction, new EloquentCollection([$page->fresh()]));
    expect($profile->refresh()->include_in_ai_index)->toBeFalse();

    evaluateSeoSuiteBulkAction($bulkIncludeAction, new EloquentCollection([$page->fresh()]));
    expect($profile->refresh()->include_in_ai_index)->toBeTrue();
});

it('builds markdown urls for included ai discovery pages with public urls', function (): void {
    foreach (['profiles', 'siteProfiles', 'readinessIssueCounts', 'markdownDiscoverability'] as $propertyName) {
        $property = new ReflectionProperty(AiDiscoveryTable::class, $propertyName);
        $property->setValue(null, []);
    }

    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language, siteDomainData: ['domain' => 'example.test', 'scheme' => 'https', 'path' => null, 'default' => true])
        ->create();
    $page = Page::factory()
        ->site($site)
        ->type(Blueprint::factory()->page()->create(['status' => true]))
        ->withTranslations($language, ['title' => 'AI Discovery Public Page'])
        ->create();

    PageUrl::factory()
        ->site($site)
        ->language($language)
        ->page($page)
        ->state(['url' => '/public-page'])
        ->create();
    PageUrl::query()
        ->where('pageable_id', $page->getKey())
        ->where('pageable_type', $page->getMorphClass())
        ->update(['url' => '/public-page']);

    ResolveAiDiscoveryProfileAction::run($site, $language)->update(['markdown_pages_enabled' => true]);
    UpdateAiDiscoveryPageInclusionAction::run($page, $site, $language, true);

    $markdownUrlFor = new ReflectionMethod(AiDiscoveryTable::class, 'markdownUrlFor');

    expect($markdownUrlFor->invoke(null, $page->fresh()))->toBe('https://example.test/public-page.md');
});

it('keeps ai discovery table profile cache scoped to the current request', function (): void {
    resetAiDiscoveryTableCaches();

    $language = Language::factory()->create();
    $site = Site::factory()
        ->language($language)
        ->withTranslations($language)
        ->create();
    $page = Page::factory()
        ->site($site)
        ->type(Blueprint::factory()->page()->create(['status' => true]))
        ->withTranslations($language, ['title' => 'AI Discovery Cached Page'])
        ->create();
    $profile = AiDiscoveryPageProfile::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => true,
        'section' => 'Pages',
        'priority' => 500,
    ]);

    $profileFor = new ReflectionMethod(AiDiscoveryTable::class, 'profileFor');
    app()->instance('request', Request::create('/ai-discovery/first'));

    $firstProfile = $profileFor->invoke(null, $page->fresh());

    $profile->forceFill(['include_in_ai_index' => false])->save();
    app()->instance('request', Request::create('/ai-discovery/second'));

    $secondProfile = $profileFor->invoke(null, $page->fresh());

    expect($firstProfile?->include_in_ai_index)->toBeTrue()
        ->and($secondProfile?->include_in_ai_index)->toBeFalse();
});

it('exposes seo audit table columns and status filters', function (): void {
    $columnsMethod = new ReflectionMethod(SeoAuditTable::class, 'getTableColumns');
    $filtersMethod = new ReflectionMethod(SeoAuditTable::class, 'getTableFilters');

    $columnNames = reflectedComponentNames($columnsMethod);
    $filterNames = reflectedComponentNames($filtersMethod);

    expect($columnNames)->toBe([
        'name',
        'site.name',
        'seo_score',
        'critical_issues_count',
        'warning_issues_count',
        'schema_status',
        'search_preview_title',
        'creator.name',
        'created_at',
    ])
        ->and($filterNames)->toBe([
            'severity',
            'issue_key',
            'score_band',
            'schema_status',
            'robots_status',
            'canonical_status',
            'search_console_status',
            'snapshot_state',
        ]);
});

/**
 * @return array<int, string>
 */
function reflectedComponentNames(ReflectionMethod $method): array
{
    $components = $method->invoke(null);

    if (! is_iterable($components)) {
        return [];
    }

    $names = [];

    foreach ($components as $component) {
        if (! is_object($component)) {
            continue;
        }

        if (! method_exists($component, 'getName')) {
            continue;
        }

        $names[] = $component->getName();
    }

    return $names;
}

function seoSuiteTableForCoverage(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

function resetAiDiscoveryTableCaches(): void
{
    foreach (['profiles', 'siteProfiles', 'readinessIssueCounts', 'markdownDiscoverability'] as $propertyName) {
        $property = new ReflectionProperty(AiDiscoveryTable::class, $propertyName);
        $property->setValue(null, []);
    }
}

function seoSuiteTableAction(mixed $action): Action
{
    throw_unless($action instanceof Action, RuntimeException::class, 'Expected an AI Discovery table action.');

    return $action;
}

function seoSuiteBulkAction(mixed $action): BulkAction
{
    throw_unless($action instanceof BulkAction, RuntimeException::class, 'Expected an AI Discovery bulk action.');

    return $action;
}

/**
 * @param  array<array-key, mixed>  $data
 */
function evaluateSeoSuiteTableAction(Action $action, Page $record, array $data = []): void
{
    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    $action->evaluate(
        $closure,
        [
            'record' => $record,
            'action' => $action,
            'data' => $data,
        ],
        [
            Page::class => $record,
            Action::class => $action,
        ],
    );
}

/**
 * @param  EloquentCollection<int, Page>  $records
 */
function evaluateSeoSuiteBulkAction(BulkAction $action, EloquentCollection $records): void
{
    $closure = $action->getActionFunction();

    expect($closure)->not->toBeNull();

    $action->evaluate(
        $closure,
        ['records' => $records],
        [EloquentCollection::class => $records],
    );
}

it('uses translation metadata before labels for seo audit search preview titles', function (): void {
    $translation = new Translation;
    $translation->forceFill([
        'title' => 'Translation title',
        'label' => 'Translation label',
        'meta' => ['title' => 'Search title'],
    ]);

    $page = new Page;
    $page->forceFill(['name' => 'Page name']);
    $page->setRelation('translation', $translation);

    $method = new ReflectionMethod(SeoAuditTable::class, 'searchPreviewTitleFor');

    expect($method->invoke(null, $page))->toBe('Search title');

    $translation->forceFill(['meta' => []]);

    expect($method->invoke(null, $page))->toBe('Translation title');

    $page->unsetRelation('translation');

    expect($method->invoke(null, $page))->toBe('Page name');
});

it('returns translated seo audit snapshot status options', function (): void {
    $method = new ReflectionMethod(SeoAuditTable::class, 'snapshotStatusOptions');

    expect(array_keys($method->invoke(null)))->toBe([
        'passed',
        'warning',
        'missing',
        'unknown',
        'declining',
    ]);
});

it('filters ai discovery pages by missing and ready summaries', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->language($language)->withTranslations($language)->create();
    $missingSummaryPage = Page::factory()->site($site)->withTranslations($language)->create();
    $readySummaryPage = Page::factory()->site($site)->withTranslations($language)->create();

    AiDiscoveryPageProfile::query()->create([
        'page_id' => $missingSummaryPage->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => true,
        'summary' => '',
        'section' => 'Pages',
        'priority' => 500,
    ]);
    AiDiscoveryPageProfile::query()->create([
        'page_id' => $readySummaryPage->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'include_in_ai_index' => true,
        'summary' => 'Ready summary',
        'section' => 'Pages',
        'priority' => 500,
    ]);

    $missingMethod = new ReflectionMethod(AiDiscoveryTable::class, 'whereSummaryMissing');
    $readyMethod = new ReflectionMethod(AiDiscoveryTable::class, 'whereSummaryReady');
    $missingQuery = Page::query()->whereKey([$missingSummaryPage->getKey(), $readySummaryPage->getKey()]);
    $readyQuery = Page::query()->whereKey([$missingSummaryPage->getKey(), $readySummaryPage->getKey()]);

    $missingMethod->invoke(null, $missingQuery);
    $readyMethod->invoke(null, $readyQuery);

    expect($missingQuery->pluck('id')->all())->toBe([$missingSummaryPage->getKey()])
        ->and($readyQuery->pluck('id')->all())->toBe([$readySummaryPage->getKey()]);
});

it('filters seo audit pages by snapshot status and ignores blank status filters', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->language($language)->withTranslations($language)->create();
    $page = Page::factory()->site($site)->withTranslations($language)->create();

    PageSeoSnapshot::query()->create([
        'page_id' => $page->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 80,
        'critical_count' => 0,
        'warning_count' => 1,
        'notice_count' => 0,
        'passed_count' => 3,
        'schema_status' => 'warning',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'computed_at' => now(),
    ]);

    $method = new ReflectionMethod(SeoAuditTable::class, 'whereSnapshotStatus');
    $matchingQuery = Page::query()->whereKey($page);
    $blankQuery = Page::query()->whereKey($page);
    $missingQuery = Page::query()->whereKey($page);

    $method->invoke(null, $matchingQuery, 'schema_status', 'warning');
    $method->invoke(null, $blankQuery, 'schema_status', '');
    $method->invoke(null, $missingQuery, 'schema_status', 'missing');

    expect($matchingQuery->exists())->toBeTrue()
        ->and($blankQuery->exists())->toBeTrue()
        ->and($missingQuery->exists())->toBeFalse();
});

it('filters seo audit pages by severity issue score band and snapshot lifecycle state', function (): void {
    $language = Language::factory()->create();
    $site = Site::factory()->language($language)->withTranslations($language)->create();
    $criticalPage = Page::factory()->site($site)->withTranslations($language)->create(['name' => 'Critical page']);
    $warningPage = Page::factory()->site($site)->withTranslations($language)->create(['name' => 'Warning page']);
    $cleanPage = Page::factory()->site($site)->withTranslations($language)->create(['name' => 'Clean page']);
    $missingSnapshotPage = Page::factory()->site($site)->withTranslations($language)->create(['name' => 'Missing snapshot']);

    PageSeoSnapshot::query()->create([
        'page_id' => $criticalPage->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 35,
        'critical_count' => 1,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 1,
        'schema_status' => 'missing',
        'robots_status' => 'warning',
        'canonical_status' => 'missing',
        'search_console_status' => 'unknown',
        'issue_keys' => [SeoCheckKeyEnum::MetaTitle->value],
        'computed_at' => now()->subDays(2),
    ]);
    PageSeoSnapshot::query()->create([
        'page_id' => $warningPage->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 75,
        'critical_count' => 0,
        'warning_count' => 1,
        'notice_count' => 0,
        'passed_count' => 2,
        'schema_status' => 'warning',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'unknown',
        'issue_keys' => [SeoCheckKeyEnum::MetaDescription->value],
        'computed_at' => now(),
    ]);
    PageSeoSnapshot::query()->create([
        'page_id' => $cleanPage->getKey(),
        'site_id' => $site->getKey(),
        'language_id' => $language->getKey(),
        'score' => 95,
        'critical_count' => 0,
        'warning_count' => 0,
        'notice_count' => 0,
        'passed_count' => 4,
        'schema_status' => 'passed',
        'robots_status' => 'passed',
        'canonical_status' => 'passed',
        'search_console_status' => 'passed',
        'issue_keys' => [],
        'computed_at' => now(),
    ]);

    $filters = SeoAuditTable::configure(seoSuiteTableForCoverage())->getFilters();
    $pageIds = [$criticalPage->getKey(), $warningPage->getKey(), $cleanPage->getKey(), $missingSnapshotPage->getKey()];

    $criticalQuery = Page::query()->whereKey($pageIds);
    $warningQuery = Page::query()->whereKey($pageIds);
    $cleanQuery = Page::query()->whereKey($pageIds);
    $issueQuery = Page::query()->whereKey($pageIds);
    $excellentQuery = Page::query()->whereKey($pageIds);
    $goodQuery = Page::query()->whereKey($pageIds);
    $poorQuery = Page::query()->whereKey($pageIds);
    $staleQuery = Page::query()->whereKey($pageIds);
    $missingQuery = Page::query()->whereKey($pageIds);
    $scannedQuery = Page::query()->whereKey($pageIds);

    $filters['severity']->apply($criticalQuery, ['value' => 'critical']);
    $filters['severity']->apply($warningQuery, ['value' => 'warning']);
    $filters['severity']->apply($cleanQuery, ['value' => 'clean']);
    $filters['issue_key']->apply($issueQuery, ['value' => SeoCheckKeyEnum::MetaTitle->value]);
    $filters['score_band']->apply($excellentQuery, ['value' => 'excellent']);
    $filters['score_band']->apply($goodQuery, ['value' => 'good']);
    $filters['score_band']->apply($poorQuery, ['value' => 'poor']);
    $filters['snapshot_state']->apply($staleQuery, ['value' => 'stale']);
    $filters['snapshot_state']->apply($missingQuery, ['value' => 'missing']);
    $filters['snapshot_state']->apply($scannedQuery, ['value' => 'scanned']);

    expect($criticalQuery->pluck('id')->all())->toBe([$criticalPage->getKey()])
        ->and($warningQuery->pluck('id')->all())->toBe([$warningPage->getKey()])
        ->and($cleanQuery->pluck('id')->all())->toBe([$cleanPage->getKey()])
        ->and($issueQuery->pluck('id')->all())->toBe([$criticalPage->getKey()])
        ->and($excellentQuery->pluck('id')->all())->toBe([$cleanPage->getKey()])
        ->and($goodQuery->pluck('id')->all())->toBe([$warningPage->getKey()])
        ->and($poorQuery->pluck('id')->all())->toBe([$criticalPage->getKey()])
        ->and($staleQuery->pluck('id')->all())->toBe([$criticalPage->getKey()])
        ->and($missingQuery->pluck('id')->all())->toBe([$missingSnapshotPage->getKey()])
        ->and($scannedQuery->pluck('id')->sort()->values()->all())->toBe([
            $criticalPage->getKey(),
            $warningPage->getKey(),
            $cleanPage->getKey(),
        ]);
});

it('builds search ranking columns and filters opportunity metric rows', function (): void {
    $site = Site::factory()->create();
    $quickWin = seoSuiteSearchMetric($site, [
        'query' => 'cms implementation',
        'impressions' => 250,
        'ctr' => 0.05,
        'average_position' => 8.2,
        'click_delta' => 4,
    ]);
    $lowCtr = seoSuiteSearchMetric($site, [
        'query' => 'cms pricing',
        'impressions' => 800,
        'ctr' => 0.01,
        'average_position' => 2.3,
        'click_delta' => 1,
    ]);
    $declining = seoSuiteSearchMetric($site, [
        'query' => 'cms migration',
        'impressions' => 120,
        'ctr' => 0.04,
        'average_position' => 12.5,
        'click_delta' => -6,
    ]);

    $table = SearchRankingsPage::table(seoSuiteTableHarness());
    $filter = $table->getFilters()['opportunity'];

    $quickWinQuery = SearchConsoleQueryMetric::query();
    $lowCtrQuery = SearchConsoleQueryMetric::query();
    $decliningQuery = SearchConsoleQueryMetric::query();
    $blankQuery = SearchConsoleQueryMetric::query();

    $filter->apply($quickWinQuery, ['value' => 'quick_win']);
    $filter->apply($lowCtrQuery, ['value' => 'ctr']);
    $filter->apply($decliningQuery, ['value' => 'declining']);
    $filter->apply($blankQuery, ['value' => null]);

    expect(array_keys($table->getColumns()))->toContain('site.name', 'query', 'url', 'clicks', 'impressions', 'ctr', 'average_position', 'click_delta')
        ->and(array_keys($table->getFilters()))->toBe(['opportunity'])
        ->and($quickWinQuery->pluck('id')->all())->toContain($quickWin->getKey(), $declining->getKey())
        ->and($lowCtrQuery->pluck('id')->all())->toBe([$lowCtr->getKey()])
        ->and($decliningQuery->pluck('id')->all())->toBe([$declining->getKey()])
        ->and($blankQuery->count())->toBe(3);
});

function seoSuiteTableHarness(): Table
{
    $livewire = Mockery::mock(HasTable::class);
    $livewire->shouldIgnoreMissing();
    $livewire->shouldReceive('makeFilamentTranslatableContentDriver')->andReturn(null)->byDefault();
    $livewire->shouldReceive('getTableFilterState')->andReturn([])->byDefault();
    $livewire->shouldReceive('isTableLoaded')->andReturnTrue()->byDefault();
    $livewire->shouldReceive('getTableArguments')->andReturn([])->byDefault();

    return Table::make($livewire);
}

/**
 * @param  array<string, mixed>  $attributes
 */
function seoSuiteSearchMetric(Site $site, array $attributes): SearchConsoleQueryMetric
{
    $query = (string) $attributes['query'];
    $url = 'https://example.test/' . str_replace(' ', '-', $query);

    /** @var SearchConsoleQueryMetric $metric */
    $metric = SearchConsoleQueryMetric::query()->create([
        'site_id' => $site->getKey(),
        'query' => $query,
        'query_hash' => hash('sha256', $query),
        'url' => $url,
        'url_hash' => hash('sha256', $url),
        'clicks' => 10,
        'impressions' => $attributes['impressions'],
        'ctr' => $attributes['ctr'],
        'average_position' => $attributes['average_position'],
        'click_delta' => $attributes['click_delta'],
        'impression_delta' => 0,
        'position_delta' => 0,
        'window_start' => now()->subDays(7)->toDateString(),
        'window_end' => now()->toDateString(),
        'synced_at' => now(),
    ]);

    return $metric;
}
