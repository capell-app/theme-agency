<?php

declare(strict_types=1);

use Capell\Admin\Contracts\Dashboard\ContentHealthDataProvider;
use Capell\Admin\Contracts\Dashboard\MyWorkQueueDataProvider;
use Capell\Admin\Contracts\Dashboard\RecentlyPublishedDataProvider;
use Capell\Admin\Contracts\Dashboard\SiteStatsDataProvider;
use Capell\Admin\Support\Dashboard\DefaultSiteStatsDataProvider;
use Capell\Admin\Support\Dashboard\NullContentHealthDataProvider;
use Capell\Admin\Support\Dashboard\NullMyWorkQueueDataProvider;
use Capell\Admin\Support\Dashboard\NullRecentlyPublishedDataProvider;
use Capell\Admin\Support\Extensions\ExtensionManagementSurfaceRegistry;
use Capell\Core\Models\Page;
use Capell\Core\Support\Settings\SettingsSchemaRegistry;
use Capell\MigrationAssistant\Contracts\MigrationAssistantContextResolver;
use Capell\PublishingStudio\Actions\DashboardReports\BuildContentSchedulerEventsAction;
use Capell\PublishingStudio\Filament\Settings\PublishingStudioSettingsSchema;
use Capell\PublishingStudio\Providers\PublishingStudioServiceProvider;
use Capell\PublishingStudio\Settings\PublishingStudioSettings;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceContentHealthDataProvider;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceMyWorkQueueDataProvider;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceRecentlyPublishedDataProvider;
use Capell\PublishingStudio\Support\Dashboard\WorkspaceSiteStatsDataProvider;
use Capell\PublishingStudio\Support\PublishingStudioMigrationAssistantContextResolver;
use Capell\PublishingStudio\Support\WorkspaceSchema;
use Capell\PublishingStudio\WorkspaceRegistry;
use Illuminate\Support\Facades\Schema;

it('WorkspaceRegistry has Page registered after boot', function (): void {
    expect(WorkspaceRegistry::isRegistered(Page::class))->toBeTrue();
});

it('uses workspace dashboard providers when the workspace schema is ready', function (): void {
    expect(resolve(ContentHealthDataProvider::class))->toBeInstanceOf(WorkspaceContentHealthDataProvider::class)
        ->and(resolve(MyWorkQueueDataProvider::class))->toBeInstanceOf(WorkspaceMyWorkQueueDataProvider::class)
        ->and(resolve(RecentlyPublishedDataProvider::class))->toBeInstanceOf(WorkspaceRecentlyPublishedDataProvider::class)
        ->and(resolve(SiteStatsDataProvider::class))->toBeInstanceOf(WorkspaceSiteStatsDataProvider::class);
});

it('uses the publishing-studio context resolver for migration assistant exports', function (): void {
    expect(resolve(MigrationAssistantContextResolver::class))
        ->toBeInstanceOf(PublishingStudioMigrationAssistantContextResolver::class);
});

it('falls back to core dashboard providers when the workspace schema is missing', function (): void {
    (new ReflectionProperty(WorkspaceSchema::class, 'tableExists'))->setValue(null, []);
    (new ReflectionProperty(WorkspaceSchema::class, 'columnExists'))->setValue(null, []);

    Schema::shouldReceive('hasTable')->with('workspaces')->andReturnFalse();
    Schema::shouldReceive('hasTable')->with('publishing_scheduler_events')->andReturnFalse();

    expect(resolve(ContentHealthDataProvider::class))->toBeInstanceOf(NullContentHealthDataProvider::class)
        ->and(resolve(MyWorkQueueDataProvider::class))->toBeInstanceOf(NullMyWorkQueueDataProvider::class)
        ->and(resolve(RecentlyPublishedDataProvider::class))->toBeInstanceOf(NullRecentlyPublishedDataProvider::class)
        ->and(resolve(SiteStatsDataProvider::class))->toBeInstanceOf(DefaultSiteStatsDataProvider::class)
        ->and(BuildContentSchedulerEventsAction::run(sourceType: 'workspace'))->toHaveCount(0);
});

it('registers publishing studio settings and extension settings surface', function (): void {
    $settingsRegistry = resolve(SettingsSchemaRegistry::class);

    expect($settingsRegistry->getSettingsClass('publishing_studio'))->toBe(PublishingStudioSettings::class)
        ->and($settingsRegistry->getSchemas('publishing_studio'))->toContain(PublishingStudioSettingsSchema::class);

    $surfaces = resolve(ExtensionManagementSurfaceRegistry::class)
        ->surfacesForPackage(PublishingStudioServiceProvider::$packageName);

    expect($surfaces[0]->settingsGroup ?? null)->toBe('publishing_studio');
});
