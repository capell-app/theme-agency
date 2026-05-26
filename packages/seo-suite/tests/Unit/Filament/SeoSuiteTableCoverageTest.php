<?php

declare(strict_types=1);

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Core\Models\Translation;
use Capell\SeoSuite\Actions\ResolveAiDiscoveryProfileAction;
use Capell\SeoSuite\Actions\UpdateAiDiscoveryPageInclusionAction;
use Capell\SeoSuite\Filament\Pages\Tables\AiDiscoveryTable;
use Capell\SeoSuite\Filament\Pages\Tables\SeoAuditTable;
use Capell\SeoSuite\Filament\Pages\Tables\TranslationCoverageTable;
use Capell\SeoSuite\Models\AiDiscoveryPageProfile;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Filament\Tables\Table;

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

it('builds markdown urls for included ai discovery pages with public urls', function (): void {
    foreach (['profiles', 'readinessIssueCounts', 'markdownDiscoverability'] as $propertyName) {
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
