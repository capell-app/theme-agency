<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\DashboardReports\BuildVisibleEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\Workflow\BuildPublishingWorkflowAttentionItemsAction;
use Capell\PublishingStudio\Filament\Pages\PublishingWorkflowPage;
use Capell\PublishingStudio\Manifest\PublishingWorkflowPageContribution;

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
