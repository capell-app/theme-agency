<?php

declare(strict_types=1);

use Capell\PublishingStudio\Actions\CreateSchedulerIcalTokenAction;
use Capell\PublishingStudio\Checks\PublishCheckResult;
use Capell\PublishingStudio\Checks\PublishCheckSeverity;
use Capell\PublishingStudio\CloneOptions;
use Capell\PublishingStudio\Data\Dashboard\MergeHistoryEntryData;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceActivityData;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceMergeData;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceMergeHistoryData;
use Capell\PublishingStudio\Data\PublishReadinessData;
use Capell\PublishingStudio\Data\ReleaseWorkspaceItemData;
use Capell\PublishingStudio\Data\ReleaseWorkspaceReadinessData;
use Capell\PublishingStudio\Data\ReleaseWorkspaceSummaryData;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Data\Workflow\PublishingWorkflowActionData;
use Capell\PublishingStudio\Data\Workflow\PublishingWorkflowPanelData;
use Capell\PublishingStudio\Data\WorkspaceSchedulerMetadataData;
use Capell\PublishingStudio\Data\WorkspaceSettingsData;
use Capell\PublishingStudio\DryRunReport;
use Capell\PublishingStudio\Enums\SchedulerDeliveryStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Enums\SchedulerIcalFeedScopeEnum;
use Capell\PublishingStudio\Health\PublishingStudioHealthCheck;
use Capell\PublishingStudio\Models\SchedulerIcalToken;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Services\MediaDiffService;
use Carbon\CarbonImmutable;
use Spatie\LaravelData\DataCollection;

it('serializes scheduler and workflow data for publishing dashboards', function (): void {
    $scheduledFor = CarbonImmutable::parse('2026-05-20 10:00:00', 'UTC');
    $event = new SchedulerEventData(
        id: 'workspace:1:publish',
        sourceType: Workspace::class,
        sourceId: 1,
        title: 'Publish home page',
        eventType: SchedulerEventTypeEnum::Publish,
        scheduledFor: $scheduledFor,
        status: 'scheduled',
        state: SchedulerEventStateEnum::Scheduled,
        siteId: 2,
        siteName: 'Main',
    );
    $action = new PublishingWorkflowActionData('Needs review', 3, 'warning', 'Editor', 'Review', '/admin/review');
    $panel = new PublishingWorkflowPanelData('review', 'Review', 'Needs attention', [$action]);

    expect($event->toTableRecord())->toMatchArray([
        'id' => 'workspace:1:publish',
        'event_type' => SchedulerEventTypeEnum::Publish->value,
        'state' => SchedulerEventStateEnum::Scheduled->value,
        'site_name' => 'Main',
    ])
        ->and($action->toArray())->not->toHaveKey('permission')
        ->and($panel->totalCount())->toBe(3);
});

it('keeps release workspace summaries and dashboard rows typed', function (): void {
    $item = new ReleaseWorkspaceItemData('draftable', 'Home', Workspace::class, 1, 'updated', 'ready', '/home');
    $summary = new ReleaseWorkspaceSummaryData(10, [$item], 1);
    $readiness = new ReleaseWorkspaceReadinessData(10, false, ['Needs approval'], 1);
    $publishReadiness = new PublishReadinessData(10, false, 2, [Workspace::class => 2], [], 0, [], null, ['Needs approval'], 1);
    $merge = new WorkspaceMergeData(10, 'Release', 'Ben', 2, 6, '2026-05-20T10:00:00+00:00');
    $historyEntry = new MergeHistoryEntryData(10, 'Release', 'Ben', 2, 6, '2026-05-20T10:00:00+00:00');
    $history = new WorkspaceMergeHistoryData(MergeHistoryEntryData::collect([$historyEntry], DataCollection::class));
    $activity = new WorkspaceActivityData(1, 0, WorkspaceMergeData::collect([$merge], DataCollection::class));
    $metadata = new WorkspaceSchedulerMetadataData(CarbonImmutable::parse('2026-05-21'), null, null, null, 'Europe/London');
    $settings = new WorkspaceSettingsData(requiredApprovalLevels: 3);
    $cloneOptions = new CloneOptions(newName: 'Copy', newSlug: 'copy');

    expect($summary->items[0]->label)->toBe('Home')
        ->and($readiness->blockingIssues)->toBe(['Needs approval'])
        ->and($publishReadiness->totalRows)->toBe(2)
        ->and($publishReadiness->blockingIssueCount)->toBe(1)
        ->and($history->entries)->toHaveCount(1)
        ->and($activity->recentMerges)->toHaveCount(1)
        ->and($metadata->displayTimezone)->toBe('Europe/London')
        ->and($settings->requiredApprovalLevels)->toBe(3)
        ->and($cloneOptions->newSlug)->toBe('copy');
});

it('reports dry run aggregate state without mutating publishing models', function (): void {
    $workspace = new Workspace;
    $error = new PublishCheckResult(
        identifier: 'seo',
        label: 'SEO',
        severity: PublishCheckSeverity::Error,
        messages: ['Missing title'],
    );
    $cleanWarning = new PublishCheckResult('links', 'Links', PublishCheckSeverity::Warn);
    $report = new DryRunReport(
        workspace: $workspace,
        wouldPublish: false,
        rebaseReport: null,
        collisions: [['site_id' => 1, 'language_id' => 1, 'url' => '/home']],
        rowCounts: [Workspace::class => 4],
        failure: new RuntimeException('No'),
        checkResults: [$error, $cleanWarning],
    );

    expect($error->isError())->toBeTrue()
        ->and($error->isClean())->toBeFalse()
        ->and($cleanWarning->isClean())->toBeTrue()
        ->and($report->totalRows())->toBe(4)
        ->and($report->hasCollisions())->toBeTrue()
        ->and($report->hasConflicts())->toBeFalse()
        ->and($report->hasBlockingCheckErrors())->toBeTrue();
});

it('covers scheduler token, media diff, delivery labels, and health boundaries', function (): void {
    $owner = $this->createUser();

    $plainToken = CreateSchedulerIcalTokenAction::run($owner, SchedulerIcalFeedScopeEnum::Mine);
    $storedToken = SchedulerIcalToken::query()->firstOrFail();
    $diff = (new MediaDiffService)->compare('/media/before.jpg', '/media/after.jpg');

    expect($plainToken)->toBeString()
        ->and(strlen((string) $plainToken))->toBe(48)
        ->and($storedToken->token_hash)->toBe(hash('sha256', (string) $plainToken))
        ->and($storedToken->scope)->toBe(SchedulerIcalFeedScopeEnum::Mine)
        ->and((new MediaDiffService)->looksLikeMedia('/uploads/photo.webp?v=1'))->toBeTrue()
        ->and((new MediaDiffService)->looksLikeMedia('not-media.txt'))->toBeFalse()
        ->and($diff->beforeUrl)->toBe('/media/before.jpg')
        ->and($diff->afterUrl)->toBe('/media/after.jpg')
        ->and($diff->contentChanged)->toBeTrue()
        ->and(SchedulerDeliveryStateEnum::Failed->getColor())->toBe('danger')
        ->and(SchedulerDeliveryStateEnum::Completed->getColor())->toBe('success')
        ->and(SchedulerDeliveryStateEnum::Snoozed->getLabel())->toBeString()
        ->and(PublishingStudioHealthCheck::compatibleCapellApiVersion())->toBe('^4.0');
});
