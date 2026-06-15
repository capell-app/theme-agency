<?php

declare(strict_types=1);

use Capell\Core\Contracts\Extensions\ChecksExtensionHealth;
use Capell\Core\Contracts\Extensions\ExtensionContribution;
use Capell\Core\Contracts\Extensions\RegistersExtensionSetting;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\Workflow\BuildPublishingWorkflowAttentionItemsAction;
use Capell\PublishingStudio\Console\Commands\InstallCommand;
use Capell\PublishingStudio\Console\Commands\LoadTestPublishingStudioCommand;
use Capell\PublishingStudio\Console\Commands\PruneAbandonedPublishingStudioCommand;
use Capell\PublishingStudio\Filament\Pages\PublishingWorkflowPage;
use Capell\PublishingStudio\Filament\Pages\ScheduledPublishingPage;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use Capell\PublishingStudio\Filament\Widgets\ContentSchedulerCalendarWidget;
use Capell\PublishingStudio\Health\PublishingStudioHealthCheck;
use Capell\PublishingStudio\Manifest\ContentSchedulerOverviewStatsContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioAdminResourcesContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioConsoleCommandsContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioDashboardWidgetsContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioHealthContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioPruneScheduleContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioRoutesContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioSettingsContribution;
use Capell\PublishingStudio\Manifest\PublishingWorkflowPageContribution;
use Capell\PublishingStudio\Manifest\ScheduledPublishingJobContribution;
use Capell\PublishingStudio\Manifest\ScheduledPublishingPageContribution;
use Capell\PublishingStudio\Settings\PublishingStudioSettings;

it('declares the publishing workflow as manifest contributions', function (): void {
    $manifest = publishingStudioLifecycleManifest();

    $contributions = capell_test_collect(publishingStudioLifecycleManifestContributions($manifest));

    expect($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-page'
            && ($contribution['class'] ?? null) === PublishingWorkflowPageContribution::class
            && ($contribution['pageClass'] ?? null) === PublishingWorkflowPage::class
            && ($contribution['labelKey'] ?? null) === 'capell-publishing-studio::workflow.navigation.label'))
        ->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'workflow-attention'
            && ($contribution['class'] ?? null) === BuildPublishingWorkflowAttentionItemsAction::class
            && ($contribution['pageClass'] ?? null) === PublishingWorkflowPage::class
            && ($contribution['labelKey'] ?? null) === 'capell-publishing-studio::workflow.dashboard.label'
            && ($contribution['permission'] ?? null) === 'View:PublishingWorkflowPage'))
        ->toBeTrue()
        ->and($manifest['permissions'] ?? [])
        ->toContain('View:PublishingWorkflowPage')
        ->toContain('View:ScheduledPublishingPage')
        ->toContain('View:StaleDraftsPage');
});

it('keeps the full publishing lifecycle visible in package capabilities', function (): void {
    $manifest = publishingStudioLifecycleManifest();

    expect($manifest['capabilities'] ?? [])
        ->toContain('live-preview')
        ->toContain('approval-history')
        ->toContain('scheduled-publishing')
        ->toContain('editorial-calendar-aggregation')
        ->toContain('editorial-calendar-contributors')
        ->toContain('editorial-calendar-view')
        ->toContain('rollback-restore')
        ->toContain('version-history')
        ->toContain('preview-link-management')
        ->toContain('field-comments')
        ->toContain('review-assignments');

    expect($manifest['actions'] ?? [])
        ->toHaveKey('buildEditorialCalendarEvents', BuildEditorialCalendarEventsAction::class)
        ->toHaveKey('buildVisibleEditorialCalendarEvents', BuildVisibleEditorialCalendarEventsAction::class);
});

it('declares editorial calendar surfaces across scheduler and package contributors', function (): void {
    $manifest = publishingStudioLifecycleManifest();

    $contributions = capell_test_collect(publishingStudioLifecycleManifestContributions($manifest));

    expect($manifest['dependencies']['supports'] ?? [])->toContain(
        'capell-app/blog',
        'capell-app/campaign-studio',
        'capell-app/events',
        'capell-app/newsletter',
    )
        ->and($manifest['contributionTraceability']['deferredContributions'] ?? null)->toBe([])
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-page'
            && ($contribution['class'] ?? null) === ScheduledPublishingPageContribution::class
            && ($contribution['pageClass'] ?? null) === ScheduledPublishingPage::class))
        ->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'admin-resource'
            && ($contribution['class'] ?? null) === PublishingStudioAdminResourcesContribution::class
            && in_array(WorkspaceResource::class, $contribution['resourceClasses'] ?? [], true)))
        ->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'dashboard-widget'
            && ($contribution['class'] ?? null) === PublishingStudioDashboardWidgetsContribution::class
            && in_array(ContentSchedulerCalendarWidget::class, $contribution['widgetClasses'] ?? [], true)))
        ->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'overview-stat'
            && ($contribution['class'] ?? null) === ContentSchedulerOverviewStatsContribution::class
            && in_array('content_scheduler.publish', $contribution['keys'] ?? [], true)))
        ->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'route'
            && ($contribution['class'] ?? null) === PublishingStudioRoutesContribution::class
            && in_array('capell-publishing-studio.scheduler.ical', $contribution['routes'] ?? [], true)))
        ->toBeTrue()
        ->and($contributions->contains(fn (array $contribution): bool => ($contribution['type'] ?? null) === 'scheduled-job'
            && ($contribution['class'] ?? null) === ScheduledPublishingJobContribution::class
            && ($contribution['name'] ?? null) === 'capell-publishing-studio-scheduled-publish'))
        ->toBeTrue()
        ->and($manifest['actions'] ?? [])
        ->toHaveKey('buildContentSchedulerEvents')
        ->toHaveKey('buildVisibleContentSchedulerEvents')
        ->toHaveKey('runDueSchedulerEvents')
        ->toHaveKey('syncWorkspaceSchedulerEvents');
});

it('declares operational package metadata for commands settings and health', function (): void {
    $manifest = publishingStudioLifecycleManifest();
    $contributions = capell_test_collect(publishingStudioLifecycleManifestContributions($manifest));
    $commands = publishingStudioLifecycleManifestCommands($manifest);

    $consoleCommand = $contributions->firstWhere('class', PublishingStudioConsoleCommandsContribution::class);
    $settings = $contributions->firstWhere('class', PublishingStudioSettingsContribution::class);
    $healthCheck = $contributions->firstWhere('class', PublishingStudioHealthContribution::class);
    $pruneSchedule = $contributions->firstWhere('class', PublishingStudioPruneScheduleContribution::class);
    throw_unless(is_array($consoleCommand), RuntimeException::class, 'Expected Publishing Studio console command contribution.');
    throw_unless(is_array($settings), RuntimeException::class, 'Expected Publishing Studio settings contribution.');
    throw_unless(is_array($healthCheck), RuntimeException::class, 'Expected Publishing Studio health contribution.');
    throw_unless(is_array($pruneSchedule), RuntimeException::class, 'Expected Publishing Studio prune schedule contribution.');

    expect($consoleCommand['commands'])->toBe([
        'capell:install-publishing-studio',
        'capell:publishing-studio:load-test',
        'capell:publishing-studio:prune',
    ])
        ->and($consoleCommand['commandClasses'])->toBe([
            InstallCommand::class,
            LoadTestPublishingStudioCommand::class,
            PruneAbandonedPublishingStudioCommand::class,
        ])
        ->and($commands['loadTest'])->toBe('capell:publishing-studio:load-test')
        ->and($commands['prune'])->toBe('capell:publishing-studio:prune')
        ->and($pruneSchedule['command'])->toBe('capell:publishing-studio:prune')
        ->and($settings['settingsClass'])->toBe(PublishingStudioSettings::class)
        ->and($settings['settingsGroup'])->toBe('publishing_studio')
        ->and($healthCheck['checkClass'])->toBe(PublishingStudioHealthCheck::class)
        ->and(class_implements(PublishingStudioConsoleCommandsContribution::class))->toContain(ExtensionContribution::class)
        ->and(class_implements(PublishingStudioSettingsContribution::class))->toContain(RegistersExtensionSetting::class)
        ->and(class_implements(PublishingStudioHealthContribution::class))->toContain(ChecksExtensionHealth::class);
});

/**
 * @return array<string, mixed>
 */
function publishingStudioLifecycleManifest(): array
{
    $manifest = capell_json_file_array(__DIR__ . '/../../capell.json');

    return publishingStudioLifecycleStringKeyedArray($manifest);
}

/**
 * @param  array<string, mixed>  $manifest
 * @return array<array-key, mixed>
 */
function publishingStudioLifecycleManifestContributions(array $manifest): array
{
    $contributes = $manifest['contributes'] ?? null;

    throw_unless(is_array($contributes), RuntimeException::class, 'Expected Publishing Studio manifest contributions.');

    return $contributes;
}

/**
 * @param  array<string, mixed>  $manifest
 * @return array<string, mixed>
 */
function publishingStudioLifecycleManifestCommands(array $manifest): array
{
    $commands = $manifest['commands'] ?? null;

    throw_unless(is_array($commands), RuntimeException::class, 'Expected Publishing Studio manifest commands.');

    return publishingStudioLifecycleStringKeyedArray($commands);
}

/**
 * @param  array<array-key, mixed>  $items
 * @return array<string, mixed>
 */
function publishingStudioLifecycleStringKeyedArray(array $items): array
{
    $stringKeyedItems = [];

    foreach ($items as $key => $value) {
        throw_unless(is_string($key), RuntimeException::class, 'Expected Publishing Studio manifest command keys to be strings.');

        $stringKeyedItems[$key] = $value;
    }

    return $stringKeyedItems;
}
