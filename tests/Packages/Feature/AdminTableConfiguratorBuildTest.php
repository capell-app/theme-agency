<?php

declare(strict_types=1);

use Capell\Address\Filament\Resources\Addresses\Tables\AddressesTable;
use Capell\Address\Filament\Resources\Countries\Tables\CountriesTable;
use Capell\Admin\Filament\Contracts\TableConfigurator;
use Capell\Blog\Filament\Resources\Articles\Tables\ArticlePagesTable;
use Capell\CampaignStudio\Filament\Resources\CampaignConversionGoals\Tables\CampaignConversionGoalsTable;
use Capell\CampaignStudio\Filament\Resources\CampaignCtaBlocks\Tables\CampaignCtaBlocksTable;
use Capell\CampaignStudio\Filament\Resources\CampaignGroups\Tables\CampaignGroupsTable;
use Capell\CampaignStudio\Filament\Resources\CampaignLandingPages\Tables\CampaignLandingPagesTable;
use Capell\Comments\Filament\Resources\CommentAuthors\CommentAuthorResource;
use Capell\Comments\Filament\Resources\Comments\CommentResource;
use Capell\ContentSections\Filament\Resources\Sections\Tables\SectionSelectionTable;
use Capell\ContentSections\Filament\Resources\Sections\Tables\SectionsTable;
use Capell\Core\Facades\CapellCore;
use Capell\Core\Models\Layout;
use Capell\Diagnostics\Filament\Pages\Tables\PermissionAuditTable;
use Capell\Diagnostics\Filament\Pages\Tables\QueueHealthTable;
use Capell\Events\Filament\Resources\Events\Tables\EventsTable;
use Capell\HtmlCache\Filament\Resources\CachedModelUrls\Tables\CachedModelUrlsTable;
use Capell\HtmlCache\Models\CachedModelUrl;
use Capell\LayoutBuilder\Filament\Resources\Layouts\Tables\LayoutsTable as LayoutBuilderLayoutsTable;
use Capell\LayoutBuilder\Filament\Resources\Pages\Tables\PageSelectionTable;
use Capell\LayoutBuilder\Filament\Resources\Widgets\Tables\WidgetAssetsTable;
use Capell\LayoutBuilder\Filament\Resources\Widgets\Tables\WidgetSelectionTable;
use Capell\LayoutBuilder\Filament\Resources\Widgets\Tables\WidgetsTable;
use Capell\LayoutBuilder\Models\Widget;
use Capell\LoginAudit\Filament\Resources\LoginAudits\Tables\LoginAuditsTable;
use Capell\MediaLibrary\Filament\Pages\Tables\MediaHealthTable;
use Capell\MigrationAssistant\Filament\Resources\ImportSessions\Tables\ImportSessionsTable;
use Capell\Navigation\Filament\Resources\Navigations\Tables\NavigationsTable;
use Capell\Newsletter\Filament\Resources\FormMappings\FormMappingResource;
use Capell\Newsletter\Filament\Resources\ProviderAudiences\ProviderAudienceResource;
use Capell\Newsletter\Filament\Resources\ProviderInterestMappings\ProviderInterestMappingResource;
use Capell\Newsletter\Filament\Resources\Segments\SegmentResource;
use Capell\Newsletter\Filament\Resources\Subscribers\SubscriberResource;
use Capell\Newsletter\Filament\Resources\SyncAttempts\SyncAttemptResource;
use Capell\PublicActions\Filament\Resources\IntegrationTokens\PublicActionIntegrationTokenResource;
use Capell\PublicActions\Models\PublicActionIntegrationToken;
use Capell\PublicActions\Providers\PublicActionsServiceProvider;
use Capell\PublishingStudio\Filament\Pages\Tables\ActivityTrailTable;
use Capell\PublishingStudio\Filament\Pages\Tables\StaleDraftsTable;
use Capell\PublishingStudio\Filament\Resources\PreviewLinks\Tables\PreviewLinksTable;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\Tables\PublishingStudioTable;
use Capell\SeoSuite\Filament\Pages\Tables\BrokenLinksTable;
use Capell\SeoSuite\Filament\Pages\Tables\SeoAuditTable;
use Capell\SeoSuite\Filament\Pages\Tables\TranslationCoverageTable;
use Capell\Tags\Filament\Resources\Tags\Tables\TagsTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

it('builds package admin table configurators with the expected editor-facing controls', function (
    string $configuratorClass,
    array $requiredColumns,
    array $requiredFilters,
    array $requiredActions,
    array $requiredToolbarActions,
): void {
    expect(is_a($configuratorClass, TableConfigurator::class, true))->toBeTrue();

    /** @var class-string<TableConfigurator> $configuratorClass */
    $table = $configuratorClass::configure(packageAdminTableForCoverage());

    $columnNames = array_keys($table->getColumns());
    $filterNames = array_keys($table->getFilters());
    $actionNames = packageAdminTableActionNames($table->getActions());
    $toolbarActionNames = method_exists($table, 'getToolbarActions')
        ? packageAdminTableActionNames($table->getToolbarActions())
        : packageAdminTableActionNames($table->getBulkActions());

    expect($columnNames)->toContain(...$requiredColumns)
        ->and($filterNames)->toContain(...$requiredFilters)
        ->and($actionNames)->toContain(...$requiredActions)
        ->and($toolbarActionNames)->toContain(...$requiredToolbarActions);
})->with([
    'article pages table' => [
        ArticlePagesTable::class,
        ['id', 'name', 'translation.title', 'site.name', 'url'],
        ['site_id', 'layout_id', 'blueprint_id', 'tags', 'filter'],
        ['edit'],
        ['delete', 'restore', 'forceDelete'],
    ],
    'content sections table' => [
        SectionsTable::class,
        ['id', 'name', 'translation.title', 'blueprint.name', 'site.name'],
        ['site_id', 'blueprint_id', 'filter', 'publish_status'],
        ['edit'],
        ['delete', 'restore', 'forceDelete'],
    ],
    'layout widgets table' => [
        WidgetsTable::class,
        ['id', 'name', 'type.name', 'key', 'block_assets_count'],
        ['blueprint_id', 'layout_id', 'filter', 'status'],
        ['edit'],
        ['delete', 'forceDelete', 'restore'],
    ],
    'layout widget assets table' => [
        WidgetAssetsTable::class,
        ['id', 'asset.name', 'asset_type', 'pageable.name', 'updated_at'],
        ['filter'],
        ['edit'],
        ['delete'],
    ],
    'migration import sessions table' => [
        ImportSessionsTable::class,
        ['id', 'uuid', 'kind', 'status', 'user.name', 'source_filename'],
        ['kind', 'status', 'user_id'],
        ['view'],
        [],
    ],
    'publishing activity trail table' => [
        ActivityTrailTable::class,
        ['subject_type', 'event', 'causer.name', 'created_at'],
        [],
        [],
        [],
    ],
    'publishing stale drafts table' => [
        StaleDraftsTable::class,
        ['name', 'status', 'updated_at', 'days_stale'],
        ['days_threshold'],
        [],
        ['bulk-request-review', 'bulk-discard-drafts'],
    ],
    'seo broken links table' => [
        BrokenLinksTable::class,
        ['page.name', 'target_url', 'http_status', 'last_checked_at'],
        [],
        ['create_redirect'],
        [],
    ],
    'address countries table' => [
        CountriesTable::class,
        ['id', 'name', 'iso2', 'addresses_count', 'status'],
        ['status', 'trashed'],
        ['edit'],
        ['delete', 'restore', 'forceDelete'],
    ],
    'address addresses table' => [
        AddressesTable::class,
        ['id', 'name', 'address', 'status', 'country.name'],
        ['country_id', 'status', 'trashed'],
        ['edit'],
        ['delete', 'restore', 'forceDelete'],
    ],
    'events table' => [
        EventsTable::class,
        ['id', 'name', 'starts_at', 'venue.name', 'visibility'],
        [],
        ['edit', 'delete'],
        [],
    ],
    'campaign groups table' => [
        CampaignGroupsTable::class,
        ['name', 'status', 'starts_at', 'ends_at', 'conversions_count'],
        [],
        ['edit'],
        ['delete'],
    ],
    'campaign landing pages table' => [
        CampaignLandingPagesTable::class,
        ['headline', 'campaignGroup.name', 'primaryGoal.name', 'conversions_count'],
        [],
        ['edit'],
        ['delete'],
    ],
]);

it('builds the remaining package table configurators used by package admin pages', function (string $configuratorClass): void {
    expect(is_a($configuratorClass, TableConfigurator::class, true))->toBeTrue();

    /** @var class-string<TableConfigurator> $configuratorClass */
    $table = $configuratorClass::configure(packageAdminTableForCoverage());

    expect($table->getColumns())->not->toBeEmpty();
})->with([
    'campaign conversion goals' => [CampaignConversionGoalsTable::class],
    'campaign cta blocks' => [CampaignCtaBlocksTable::class],
    'content section selection' => [SectionSelectionTable::class],
    'diagnostics permission audit' => [PermissionAuditTable::class],
    'diagnostics queue health' => [QueueHealthTable::class],
    'layout page selection' => [PageSelectionTable::class],
    'layout widget selection' => [WidgetSelectionTable::class],
    'login audits' => [LoginAuditsTable::class],
    'media health' => [MediaHealthTable::class],
    'navigation navigations' => [NavigationsTable::class],
    'publishing preview links' => [PreviewLinksTable::class],
    'publishing workspaces' => [PublishingStudioTable::class],
    'tags' => [TagsTable::class],
    'seo audit' => [SeoAuditTable::class],
    'seo translation coverage' => [TranslationCoverageTable::class],
]);

it('builds the html cache map table and searches by canonical url hash', function (): void {
    Schema::dropIfExists('cached_model_urls');
    Schema::create('cached_model_urls', function (Blueprint $table): void {
        $table->id();
        $table->string('url');
        $table->string('url_hash');
        $table->string('cacheable_type');
        $table->unsignedBigInteger('cacheable_id');
        $table->unsignedBigInteger('site_id')->nullable();
        $table->unsignedBigInteger('language_id')->nullable();
        $table->unsignedBigInteger('site_domain_id')->nullable();
        $table->string('path')->nullable();
        $table->timestamp('cached_at')->nullable();
        $table->timestamp('last_seen_at')->nullable();
        $table->timestamps();
    });

    $matchingRecord = CachedModelUrl::query()->create([
        'url' => 'https://example.test/products',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/products'),
        'cacheable_type' => 'page',
        'cacheable_id' => 1,
        'path' => '/products',
        'last_seen_at' => now(),
    ]);
    CachedModelUrl::query()->create([
        'url' => 'https://example.test/about',
        'url_hash' => CachedModelUrl::hashUrl('https://example.test/about'),
        'cacheable_type' => 'page',
        'cacheable_id' => 2,
        'path' => '/about',
        'last_seen_at' => now(),
    ]);

    $table = CachedModelUrlsTable::configure(packageAdminTableForCoverage(), CachedModelUrl::query());
    $search = new ReflectionMethod(CachedModelUrlsTable::class, 'applyUrlHashSearch');

    /** @var Builder<CachedModelUrl> $query */
    $query = CachedModelUrl::query();
    $search->invoke(null, $query, 'https://example.test/products');

    expect(array_keys($table->getColumns()))->toContain('url', 'cacheable_type', 'cacheable', 'site.name', 'last_seen_at')
        ->and(array_keys($table->getFilters()))->toContain('site_id', 'language_id', 'cacheable_type')
        ->and(packageAdminTableActionNames($table->getActions()))->toContain('open_url', 'clear')
        ->and($query->pluck('id')->all())->toBe([$matchingRecord->getKey()]);
});

it('builds the layout builder layouts table with block inventory filters and info actions', function (): void {
    $hero = Widget::factory()->create(['key' => 'layout-table-hero', 'name' => 'Hero block']);
    $cards = Widget::factory()->create(['key' => 'layout-table-cards', 'name' => 'Cards block']);
    $layout = Layout::factory()->create([
        'containers' => [
            'main' => [
                'widgets' => [
                    ['widget_key' => $hero->key],
                    ['widget_key' => $cards->key],
                ],
            ],
        ],
    ]);
    Layout::factory()->create([
        'containers' => [
            'main' => [
                'widgets' => [
                    ['widget_key' => 'other-block'],
                ],
            ],
        ],
    ]);

    $table = LayoutBuilderLayoutsTable::configure(packageAdminTableForCoverage());
    $widgetBlocksForLayout = new ReflectionMethod(LayoutBuilderLayoutsTable::class, 'widgetBlocksForLayout');
    $whereContainsWidgetKey = new ReflectionMethod(LayoutBuilderLayoutsTable::class, 'whereContainsWidgetKey');

    $matchingQuery = Layout::query();
    $whereContainsWidgetKey->invoke(null, $matchingQuery, $hero->key);

    expect($table->getColumns())->not->toBeEmpty()
        ->and(array_keys($table->getFilters()))->toContain('widget_key')
        ->and(packageAdminTableActionNames($table->getActions()))->toContain('info')
        ->and($widgetBlocksForLayout->invoke(null, $layout)->pluck('name')->all())->toBe(['Hero block', 'Cards block'])
        ->and($matchingQuery->pluck('id')->all())->toBe([$layout->getKey()]);
});

it('builds package resource tables with their expected operational columns and actions', function (
    string $resourceClass,
    array $requiredColumns,
    array $requiredFilters,
    array $requiredActions,
): void {
    $table = $resourceClass::table(packageAdminTableForCoverage());
    $columnNames = array_keys($table->getColumns());
    $filterNames = array_keys($table->getFilters());
    $actionNames = packageAdminTableActionNames($table->getActions());

    expect($columnNames)->toContain(...$requiredColumns);

    if ($requiredFilters !== []) {
        expect($filterNames)->toContain(...$requiredFilters);
    }

    if ($requiredActions !== []) {
        expect($actionNames)->toContain(...$requiredActions);
    }
})->with([
    'comment moderation queue' => [
        CommentResource::class,
        ['id', 'commentable', 'author.name', 'body', 'status', 'site.name', 'submitted_at'],
        ['status'],
        ['context', 'approve', 'reject', 'spam', 'archive'],
    ],
    'comment author moderation' => [
        CommentAuthorResource::class,
        ['name', 'email', 'comments_count', 'email_verified_at', 'trusted_at', 'blocked_at'],
        [],
        ['trust', 'block', 'unblock', 'verify', 'resend_verification'],
    ],
    'newsletter form mappings' => [
        FormMappingResource::class,
        ['name', 'form_handle', 'email_field', 'updated_at'],
        [],
        [],
    ],
    'newsletter provider audiences' => [
        ProviderAudienceResource::class,
        ['name', 'providerConnection.name', 'remote_id', 'updated_at'],
        [],
        [],
    ],
    'newsletter provider interest mappings' => [
        ProviderInterestMappingResource::class,
        ['providerAudience.name', 'tag.name', 'remote_interest_id', 'remote_interest_type'],
        [],
        [],
    ],
    'newsletter segments' => [
        SegmentResource::class,
        ['name', 'handle', 'type', 'updated_at'],
        [],
        [],
    ],
    'newsletter subscribers' => [
        SubscriberResource::class,
        ['id', 'email', 'status', 'subscribed_at', 'created_at'],
        [],
        [],
    ],
    'newsletter sync attempts' => [
        SyncAttemptResource::class,
        ['operation', 'sync_status', 'attempts', 'error_message', 'last_attempted_at'],
        [],
        [],
    ],
    'public action integration tokens' => [
        PublicActionIntegrationTokenResource::class,
        ['name', 'provider', 'last_used_at', 'revoked_at'],
        ['provider'],
        ['revoke'],
    ],
]);

it('registers the public action integration token resource with its admin navigation metadata', function (): void {
    CapellCore::forcePackageInstalled(PublicActionsServiceProvider::$packageName);

    $resource = PublicActionIntegrationTokenResource::class;

    expect($resource::getModel())->toBe(PublicActionIntegrationToken::class)
        ->and($resource::getPages())->toHaveKey('index')
        ->and($resource::getNavigationGroup())->toBeString()
        ->and($resource::getNavigationParentItem())->toBeString()
        ->and($resource::shouldRegisterNavigation())->toBeTrue();
});

function packageAdminTableForCoverage(): Table
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
 * @param  array<array-key, mixed>  $actions
 * @return array<int, string>
 */
function packageAdminTableActionNames(array $actions): array
{
    return collect($actions)
        ->flatten()
        ->filter(fn (mixed $action): bool => is_object($action) && method_exists($action, 'getName'))
        ->map(fn (object $action): string => $action->getName())
        ->values()
        ->all();
}
