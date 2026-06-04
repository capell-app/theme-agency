<?php

declare(strict_types=1);

use Capell\Admin\Support\CapellAdminManager;
use Capell\Admin\Support\Extensions\ExtensionPageRegistry;
use Capell\Core\Contracts\RedirectResolver;
use Capell\Core\Data\RedirectDecisionData;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Language;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\Site;
use Capell\Frontend\Support\Routing\FrontendRouteMiddlewareRegistry;
use Capell\UrlManager\Actions\BuildRedirectRulesCsvAction;
use Capell\UrlManager\Actions\BuildRedirectRulesCsvTemplateAction;
use Capell\UrlManager\Actions\DeleteRedirectRuleAction;
use Capell\UrlManager\Actions\ImportRedirectRulesAction;
use Capell\UrlManager\Actions\ParseRedirectRulesCsvAction;
use Capell\UrlManager\Actions\PreviewRedirectRulesImportAction;
use Capell\UrlManager\Actions\ResolveRedirectRulesCsvContentsAction;
use Capell\UrlManager\Actions\SetRedirectRuleStatusAction;
use Capell\UrlManager\Actions\UpdateRedirectRuleAction;
use Capell\UrlManager\Actions\UpsertRedirectRuleAction;
use Capell\UrlManager\Data\RedirectRuleData;
use Capell\UrlManager\Enums\RedirectMatchType;
use Capell\UrlManager\Enums\RedirectRuleStatus;
use Capell\UrlManager\Enums\UrlManagerPermission;
use Capell\UrlManager\Filament\Pages\NotFoundOpportunitiesPage;
use Capell\UrlManager\Filament\Pages\RedirectRulesPage;
use Capell\UrlManager\Filament\Pages\Tables\RedirectRulesTable;
use Capell\UrlManager\Http\Middleware\RecordNotFoundOpportunityMiddleware;
use Capell\UrlManager\Models\RedirectRule;
use Capell\UrlManager\Providers\UrlManagerServiceProvider;
use Capell\UrlManager\Support\Redirects\UrlManagerRedirectResolver;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

use function Pest\Laravel\assertDatabaseHas;

it('creates and edits redirect rules through the admin action handlers', function (): void {
    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/legacy/',
        targetUrl: '/current/',
        statusCode: 301,
        notes: 'Imported from sitemap review.',
    ));

    expect(RedirectRule::query()->count())->toBe(1)
        ->and($redirectRule->source_url)->toBe('/legacy')
        ->and($redirectRule->target_url)->toBe('/current');

    UpdateRedirectRuleAction::run($redirectRule, new RedirectRuleData(
        sourceUrl: '/legacy-updated/',
        targetUrl: '/current-updated/',
        statusCode: 308,
        preserveQuery: false,
        notes: 'Updated after launch.',
    ));

    expect($redirectRule->refresh())
        ->source_url->toBe('/legacy-updated')
        ->target_url->toBe('/current-updated')
        ->status_code->toBe(308)
        ->preserve_query->toBeFalse();
});

it('imports and exports redirect rules through the admin header action handlers', function (): void {
    $csv = <<<'CSV'
source_url,target_url,status_code,match_type,status,preserve_query,notes
/old-one,/new-one,301,exact,active,1,One
/old-two,/new-two,308,exact,inactive,0,Two
CSV;

    $result = ImportRedirectRulesAction::run(ParseRedirectRulesCsvAction::run($csv));

    expect($result->imported)->toBe(2)
        ->and($result->skipped)->toBe(0)
        ->and(RedirectRule::query()->count())->toBe(2);

    assertDatabaseHas('url_manager_redirect_rules', [
        'source_url' => '/old-one',
        'target_url' => '/new-one',
    ]);

    $exportedRows = ParseRedirectRulesCsvAction::run(BuildRedirectRulesCsvAction::run());

    expect($exportedRows)
        ->toHaveCount(2)
        ->and($exportedRows[0]['source_url'])->toBe('/old-one')
        ->and($exportedRows[1]['source_url'])->toBe('/old-two');
});

it('previews redirect imports and exposes a CSV import template', function (): void {
    $csv = <<<'CSV'
source_url,target_url,status_code,match_type,status,preserve_query,notes
/valid,/target,301,exact,active,1,Valid
/self,/self,301,exact,active,1,Invalid
CSV;

    $result = PreviewRedirectRulesImportAction::run(ParseRedirectRulesCsvAction::run($csv));
    $template = BuildRedirectRulesCsvTemplateAction::run();

    expect($result->imported)->toBe(1)
        ->and($result->skipped)->toBe(1)
        ->and($result->errors[0])->toContain('A redirect cannot point to itself.')
        ->and($template)->toContain('source_url,target_url,site_id,language_id,status_code,match_type,status,priority,preserve_query,notes');
});

it('resolves redirect imports from uploaded files or pasted csv contents', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('redirect-imports/sample.csv', "source_url,target_url\n/from-file,/target-file\n");

    $fromFile = ResolveRedirectRulesCsvContentsAction::run(['csv' => 'redirect-imports/sample.csv']);
    $fromInline = ResolveRedirectRulesCsvContentsAction::run([
        'csv' => 'redirect-imports/sample.csv',
        'csv_contents' => "source_url,target_url\n/from-inline,/target-inline\n",
    ]);

    expect($fromFile)->toContain('/from-file')
        ->and($fromInline)->toContain('/from-inline')
        ->and($fromInline)->not->toContain('/from-file');
});

it('rejects redirect import files over the configured size limit', function (): void {
    Storage::fake('local');
    Storage::disk('local')->put('redirect-imports/large.csv', str_repeat('a', 2049));

    ResolveRedirectRulesCsvContentsAction::run(['csv' => 'redirect-imports/large.csv'], maxKilobytes: 1);
})->throws(ValidationException::class);

it('activates disables and deletes redirect rules through admin action handlers', function (): void {
    $redirectRule = UpsertRedirectRuleAction::run(new RedirectRuleData(
        sourceUrl: '/legacy',
        targetUrl: '/current',
    ));

    $updated = SetRedirectRuleStatusAction::run($redirectRule, RedirectRuleStatus::Inactive);

    expect($updated)->toBe(1)
        ->and($redirectRule->refresh()->status)->toBe(RedirectRuleStatus::Inactive);

    $updated = SetRedirectRuleStatusAction::run(collect([$redirectRule]), RedirectRuleStatus::Active);

    expect($updated)->toBe(1)
        ->and($redirectRule->refresh()->status)->toBe(RedirectRuleStatus::Active);

    DeleteRedirectRuleAction::run($redirectRule);

    expect(RedirectRule::query()->count())->toBe(0);
});

it('guards url manager pages behind view or manage permissions', function (): void {
    expect(RedirectRulesPage::canAccess())->toBeFalse()
        ->and(NotFoundOpportunitiesPage::canAccess())->toBeFalse();

    Gate::define(
        UrlManagerPermission::ManageRedirectRules->value,
        static fn (?object $user = null): bool => true,
    );
    Gate::define(
        UrlManagerPermission::ViewNotFoundOpportunitiesPage->value,
        static fn (?object $user = null): bool => true,
    );

    expect(RedirectRulesPage::canAccess())->toBeTrue()
        ->and(NotFoundOpportunitiesPage::canAccess())->toBeTrue();
});

it('configures the redirect rules table with import export and lifecycle actions', function (): void {
    $table = RedirectRulesPage::table(Table::make(resolve(RedirectRulesPage::class)));
    $query = $table->getQuery();

    throw_unless($query instanceof Builder, RuntimeException::class, 'Expected redirect rules table to expose an Eloquent query.');

    expect(array_keys($table->getColumns()))
        ->toBe([
            'source_url',
            'target_url',
            'status_code',
            'match_type',
            'status',
            'priority',
            'hit_count',
            'last_hit_at',
        ])
        ->and(array_keys($table->getFilters()))
        ->toBe(['status'])
        ->and(urlManagerTableActionNames($table->getRecordActions()))
        ->toBe(['edit', 'activateRedirectRule', 'disableRedirectRule', 'delete'])
        ->and(urlManagerTableActionNames($table->getHeaderActions()))
        ->toBe([
            'create',
            'importRedirectRules',
            'previewRedirectImport',
            'exportRedirectRules',
            'downloadRedirectImportTemplate',
        ])
        ->and(urlManagerTableActionNames($table->getToolbarActions()))
        ->toBe(['activateRedirectRules', 'disableRedirectRules', 'deleteRedirectRules'])
        ->and($table->getDefaultSort($query, 'desc'))
        ->toBe('hit_count')
        ->and($table->getDefaultSortDirection())
        ->toBe('desc');
});

it('normalizes redirect rule form state into typed data for table actions', function (): void {
    $method = new ReflectionMethod(RedirectRulesTable::class, 'redirectRuleDataFromFormData');

    $data = $method->invoke(null, [
        'source_url' => '/old',
        'target_url' => '/new',
        'site_id' => '12',
        'language_id' => '',
        'status_code' => '308',
        'match_type' => RedirectMatchType::Prefix->value,
        'status' => RedirectRuleStatus::Inactive->value,
        'preserve_query' => false,
        'notes' => '  keep the query decision documented  ',
    ]);

    $defaulted = $method->invoke(null, [
        'source_url' => '/fallback',
        'target_url' => '/target',
        'status_code' => 'not-a-number',
        'notes' => '   ',
    ]);

    expect($data)->toBeInstanceOf(RedirectRuleData::class)
        ->and($data->sourceUrl)->toBe('/old')
        ->and($data->siteId)->toBe(12)
        ->and($data->languageId)->toBeNull()
        ->and($data->statusCode)->toBe(308)
        ->and($data->matchType)->toBe(RedirectMatchType::Prefix)
        ->and($data->status)->toBe(RedirectRuleStatus::Inactive)
        ->and($data->preserveQuery)->toBeFalse()
        ->and($data->notes)->toBe('  keep the query decision documented  ')
        ->and($defaulted)->toBeInstanceOf(RedirectRuleData::class)
        ->and($defaulted->statusCode)->toBe(301)
        ->and($defaulted->matchType)->toBe(RedirectMatchType::Exact)
        ->and($defaulted->status)->toBe(RedirectRuleStatus::Active)
        ->and($defaulted->preserveQuery)->toBeTrue()
        ->and($defaulted->notes)->toBeNull();
});

it('configures the not found opportunities table with conversion and triage actions', function (): void {
    $table = NotFoundOpportunitiesPage::table(Table::make(resolve(NotFoundOpportunitiesPage::class)));
    $query = $table->getQuery();

    throw_unless($query instanceof Builder, RuntimeException::class, 'Expected not found opportunities table to expose an Eloquent query.');

    expect(array_keys($table->getColumns()))
        ->toBe([
            'source_url',
            'suggested_target_url',
            'status',
            'hit_count',
            'last_seen_at',
        ])
        ->and(array_keys($table->getFilters()))
        ->toBe(['status'])
        ->and(urlManagerTableActionNames($table->getRecordActions()))
        ->toBe(['convert_to_redirect', 'ignoreOpportunity', 'reopenOpportunity'])
        ->and(urlManagerTableActionNames($table->getToolbarActions()))
        ->toBe(['ignoreOpportunities', 'reopenOpportunities'])
        ->and($table->getDefaultSort($query, 'desc'))
        ->toBe('hit_count')
        ->and($table->getDefaultSortDirection())
        ->toBe('desc');
});

it('registers installed package admin pages and frontend redirect resolver behavior', function (): void {
    CapellCore::forcePackageInstalled(UrlManagerServiceProvider::$packageName);
    expect(CapellCore::isPackageInstalled(UrlManagerServiceProvider::$packageName))->toBeTrue();

    app()->singleton(ExtensionPageRegistry::class, fn (): ExtensionPageRegistry => new ExtensionPageRegistry);
    app()->singleton(CapellAdminManager::class, fn (): CapellAdminManager => new CapellAdminManager);
    app()->bind(RedirectResolver::class, fn (): RedirectResolver => new class implements RedirectResolver
    {
        public function resolve(Site $site, Language $language, string $url, ?int $pageId = null, ?PageUrl $pageUrl = null): ?RedirectDecisionData
        {
            return null;
        }
    });

    (new UrlManagerServiceProvider(app()))->registeringPackage();

    $extensionPages = collect(resolve(ExtensionPageRegistry::class)->entries())
        ->pluck('page');

    expect($extensionPages)
        ->toContain(RedirectRulesPage::class)
        ->toContain(NotFoundOpportunitiesPage::class)
        ->and(resolve(RedirectResolver::class))->toBeInstanceOf(UrlManagerRedirectResolver::class);

    RedirectRule::query()->create([
        'site_id' => 1,
        'language_id' => 1,
        'source_url' => '/moved',
        'source_hash' => hash('sha256', '/moved'),
        'target_url' => '/settled',
        'target_hash' => hash('sha256', '/settled'),
        'status_code' => 302,
        'match_type' => 'exact',
        'status' => 'active',
        'preserve_query' => true,
    ]);

    app()->instance('request', Request::create('/moved?campaign=spring', Symfony\Component\HttpFoundation\Request::METHOD_GET));

    $decision = resolve(RedirectResolver::class)->resolve(
        site: urlManagerFilamentTestSite(),
        language: urlManagerFilamentTestLanguage(),
        url: '/moved',
    );

    expect($decision?->targetUrl)->toBe('/settled?campaign=spring')
        ->and($decision?->statusCode)->toBe(302);
});

it('registers frontend not-found capture middleware through the installed package provider', function (): void {
    CapellCore::forcePackageInstalled(UrlManagerServiceProvider::$packageName);

    app()->singleton(
        FrontendRouteMiddlewareRegistry::class,
        fn (): FrontendRouteMiddlewareRegistry => new FrontendRouteMiddlewareRegistry,
    );

    (new UrlManagerServiceProvider(app()))->registeringPackage();

    expect(resolve(FrontendRouteMiddlewareRegistry::class)->all())
        ->toContain(RecordNotFoundOpportunityMiddleware::class);
});

function urlManagerFilamentTestSite(): Site
{
    $site = new Site;
    $site->forceFill(['id' => 1]);

    return $site;
}

function urlManagerFilamentTestLanguage(): Language
{
    $language = new Language;
    $language->forceFill(['id' => 1]);

    return $language;
}

/**
 * @param  array<int|string, mixed>  $actions
 * @return array<int, string>
 */
function urlManagerTableActionNames(array $actions): array
{
    return collect($actions)
        ->flatMap(function (mixed $action): array {
            if (is_object($action) && method_exists($action, 'getFlatActions')) {
                return array_values(array_map(
                    static fn (mixed $nestedAction): string => is_object($nestedAction) && method_exists($nestedAction, 'getName')
                        ? (string) $nestedAction->getName()
                        : '',
                    $action->getFlatActions(),
                ));
            }

            return is_object($action) && method_exists($action, 'getName') ? [(string) $action->getName()] : [];
        })
        ->filter(static fn (string $name): bool => $name !== '')
        ->values()
        ->all();
}
