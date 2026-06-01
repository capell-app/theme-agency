<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\Workflow\BuildPublishingWorkflowAttentionItemsAction;
use Capell\PublishingStudio\Filament\Pages\PublishingWorkflowPage;
use Capell\PublishingStudio\Filament\Pages\ScheduledPublishingPage;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use Capell\PublishingStudio\Filament\Widgets\ContentSchedulerCalendarWidget;
use Capell\PublishingStudio\Manifest\ContentSchedulerOverviewStatsContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioAdminResourcesContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioDashboardWidgetsContribution;
use Capell\PublishingStudio\Manifest\PublishingStudioRoutesContribution;
use Capell\PublishingStudio\Manifest\PublishingWorkflowPageContribution;
use Capell\PublishingStudio\Manifest\ScheduledPublishingJobContribution;
use Capell\PublishingStudio\Manifest\ScheduledPublishingPageContribution;

it('declares the publishing workflow as manifest contributions', function (): void {
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $contributions = capell_test_collect($manifest['contributes'] ?? []);

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
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

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
    $manifest = json_decode(
        (string) file_get_contents(__DIR__ . '/../../capell.json'),
        associative: true,
        flags: JSON_THROW_ON_ERROR,
    );

    $contributions = capell_test_collect($manifest['contributes'] ?? []);

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
