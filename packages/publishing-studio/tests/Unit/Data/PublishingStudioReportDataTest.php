<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Tests\Unit\Data;

use Capell\Admin\Data\PagePublishStateData;
use Capell\Core\Models\Page;
use Capell\PublishingStudio\Activity\WorkspaceActivityEntry;
use Capell\PublishingStudio\Approvals\RequiredReviewer;
use Capell\PublishingStudio\Checks\PublishCheckResult;
use Capell\PublishingStudio\Checks\PublishCheckSeverity;
use Capell\PublishingStudio\Data\Dashboard\MergeHistoryEntryData;
use Capell\PublishingStudio\Data\Dashboard\WorkspaceMergeData;
use Capell\PublishingStudio\DryRunReport;
use Capell\PublishingStudio\Enums\WorkspaceStatusEnum;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\RebaseReport;
use Carbon\CarbonImmutable;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class PublishingStudioReportDataTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Container::getInstance()->instance('translator', new class
        {
            /**
             * @param  array<string, mixed>  $replace
             */
            public function get(string $key, array $replace = [], ?string $locale = null, bool $fallback = true): string
            {
                if ($replace === []) {
                    return $key;
                }

                return $key . ':' . json_encode($replace, JSON_THROW_ON_ERROR);
            }
        });
    }

    public function test_page_publish_state_reports_workspace_publication_and_label_state(): void
    {
        $publishedState = new PagePublishStateData(
            pageId: 10,
            isDraft: false,
            publishedAt: CarbonImmutable::parse('2026-06-01 09:00:00', 'UTC'),
            previewUrl: null,
        );

        $workspaceDraftState = new PagePublishStateData(
            pageId: 10,
            isDraft: true,
            publishedAt: null,
            previewUrl: 'https://example.test/preview',
            contextId: 7,
            contextName: 'Summer launch',
            contextStatus: WorkspaceStatusEnum::Open->getLabel(),
        );

        $this->assertFalse($publishedState->hasActiveContext());
        $this->assertTrue($publishedState->isPublished());
        $this->assertSame('capell-admin::publish_panel.status_published', $publishedState->statusLabel());
        $this->assertTrue($workspaceDraftState->hasActiveContext());
        $this->assertFalse($workspaceDraftState->isPublished());
        $this->assertSame('capell-admin::publish_panel.status_draft_in_workspace:{"workspace":"Summer launch"}', $workspaceDraftState->statusLabel());
    }

    public function test_dry_run_report_summarises_rows_collisions_conflicts_and_blocking_checks(): void
    {
        $workspace = new Workspace;
        $workspace->id = 7;
        $workspace->base_version_id = 1;

        $rebaseReport = new RebaseReport(
            workspace: $workspace,
            currentLiveVersionId: 3,
            conflicts: [],
        );
        $rebaseReport->addConflict(Workspace::class, 'workspace-uuid');

        $report = new DryRunReport(
            workspace: $workspace,
            wouldPublish: false,
            rebaseReport: $rebaseReport,
            collisions: [
                ['site_id' => 1, 'language_id' => 1, 'url' => '/about'],
            ],
            rowCounts: [
                Workspace::class => 2,
                Page::class => 3,
            ],
            failure: new RuntimeException('Publish blocked.'),
            checkResults: [
                new PublishCheckResult(
                    identifier: 'seo',
                    label: 'SEO',
                    severity: PublishCheckSeverity::Error,
                    messages: ['Missing title.'],
                ),
            ],
        );

        $this->assertSame(5, $report->totalRows());
        $this->assertTrue($report->hasCollisions());
        $this->assertTrue($report->hasConflicts());
        $this->assertTrue($report->hasBlockingCheckErrors());
    }

    public function test_dry_run_report_treats_clean_error_checks_as_non_blocking(): void
    {
        $report = new DryRunReport(
            workspace: new Workspace,
            wouldPublish: true,
            rebaseReport: null,
            collisions: [],
            rowCounts: [],
            checkResults: [
                new PublishCheckResult(
                    identifier: 'accessibility',
                    label: 'Accessibility',
                    severity: PublishCheckSeverity::Error,
                ),
            ],
        );

        $this->assertSame(0, $report->totalRows());
        $this->assertFalse($report->hasCollisions());
        $this->assertFalse($report->hasConflicts());
        $this->assertFalse($report->hasBlockingCheckErrors());
    }

    public function test_activity_reviewer_and_dashboard_rows_preserve_view_ready_values(): void
    {
        $occurredAt = CarbonImmutable::parse('2026-06-02 10:15:00', 'UTC');
        $activity = new WorkspaceActivityEntry(
            workspaceId: 7,
            workspaceName: 'Summer launch',
            description: 'Workspace submitted',
            event: 'submitted',
            causerId: 5,
            causerType: 'user',
            occurredAt: $occurredAt,
        );
        $requiredReviewer = new RequiredReviewer(
            requiredFor: 'page:landing',
            role: 'workspace_reviewer',
            reviewerType: 'user',
            reviewerId: 5,
        );
        $merge = new WorkspaceMergeData(
            workspaceId: 7,
            name: 'Summer launch',
            actorName: 'Ben',
            pageCount: 4,
            durationOpenHours: 26,
            publishedAt: '2026-06-03 12:00:00',
        );
        $historyEntry = new MergeHistoryEntryData(
            workspaceId: 7,
            name: 'Summer launch',
            actorName: 'Ben',
            pageCount: 4,
            durationOpenHours: 26,
            publishedAt: '2026-06-03 12:00:00',
        );

        $this->assertSame(7, $activity->workspaceId);
        $this->assertSame('Summer launch', $activity->workspaceName);
        $this->assertSame('Workspace submitted', $activity->description);
        $this->assertSame('submitted', $activity->event);
        $this->assertSame(5, $activity->causerId);
        $this->assertSame('user', $activity->causerType);
        $this->assertSame($occurredAt, $activity->occurredAt);
        $this->assertSame('page:landing', $requiredReviewer->requiredFor);
        $this->assertSame('workspace_reviewer', $requiredReviewer->role);
        $this->assertSame('user', $requiredReviewer->reviewerType);
        $this->assertSame(5, $requiredReviewer->reviewerId);
        $this->assertSame($merge->workspaceId, $historyEntry->workspaceId);
        $this->assertSame($merge->name, $historyEntry->name);
        $this->assertSame($merge->actorName, $historyEntry->actorName);
        $this->assertSame($merge->pageCount, $historyEntry->pageCount);
        $this->assertSame($merge->durationOpenHours, $historyEntry->durationOpenHours);
        $this->assertSame($merge->publishedAt, $historyEntry->publishedAt);
    }
}
