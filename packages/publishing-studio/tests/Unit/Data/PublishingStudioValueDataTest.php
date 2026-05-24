<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Tests\Unit\Data;

use Capell\PublishingStudio\Checks\PublishCheckResult;
use Capell\PublishingStudio\Checks\PublishCheckSeverity;
use Capell\PublishingStudio\CloneOptions;
use Capell\PublishingStudio\Data\SchedulerEventData;
use Capell\PublishingStudio\Data\WorkspaceSettingsData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Rollback\EntityRollbackReport;
use Capell\PublishingStudio\Services\MediaDiffResult;
use Carbon\CarbonImmutable;
use Illuminate\Container\Container;
use Iterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @extends TestCase<MockObject> */
final class PublishingStudioValueDataTest extends TestCase
{
    /**
     * @return Iterator<string, array{(string | null), (string | null), bool}>
     */
    public static function mediaDiffProvider(): Iterator
    {
        yield 'no media' => [null, null, false];
        yield 'before only' => ['https://example.test/before.jpg', null, true];
        yield 'after only' => [null, 'https://example.test/after.jpg', true];
        yield 'both sides' => ['https://example.test/before.jpg', 'https://example.test/after.jpg', true];
    }

    public function test_clone_options_default_to_copying_draft_content_and_settings(): void
    {
        $options = new CloneOptions;

        $this->assertTrue($options->copyDrafts);
        $this->assertTrue($options->copySettings);
        $this->assertNull($options->newName);
        $this->assertNull($options->newSlug);
        $this->assertNull($options->description);
    }

    public function test_workspace_settings_default_to_two_approval_levels(): void
    {
        $this->assertSame(2, (new WorkspaceSettingsData)->requiredApprovalLevels);
    }

    public function test_scheduler_event_table_record_includes_labels_colours_and_source_metadata(): void
    {
        Container::getInstance()->instance('translator', new class
        {
            /**
             * @param  array<string, mixed>  $replace
             */
            public function get(string $key, array $replace = [], ?string $locale = null, bool $fallback = true): string
            {
                return $key;
            }
        });

        $scheduledFor = CarbonImmutable::parse('2026-06-01 09:30:00', 'UTC');
        $event = new SchedulerEventData(
            id: 'workspace:42:publish',
            sourceType: 'workspace',
            sourceId: 42,
            title: 'Summer launch',
            eventType: SchedulerEventTypeEnum::Publish,
            scheduledFor: $scheduledFor,
            status: 'scheduled',
            description: 'Ready to publish.',
            recordUrl: 'https://example.test/admin/workspaces/42',
            state: SchedulerEventStateEnum::Scheduled,
            siteId: 3,
            siteName: 'Main site',
            ownerId: 7,
            ownerName: 'Editor',
            timezone: 'Europe/London',
        );

        $this->assertSame([
            'id' => 'workspace:42:publish',
            'source_type' => 'workspace',
            'source_id' => 42,
            'title' => 'Summer launch',
            'event_type' => 'publish',
            'event_type_label' => 'capell-publishing-studio::scheduler.event_types.publish',
            'event_type_color' => 'success',
            'scheduled_for' => $scheduledFor,
            'status' => 'scheduled',
            'description' => 'Ready to publish.',
            'record_url' => 'https://example.test/admin/workspaces/42',
            'state' => 'scheduled',
            'state_label' => 'capell-publishing-studio::scheduler.states.scheduled',
            'state_color' => 'info',
            'site_id' => 3,
            'site_name' => 'Main site',
            'owner_id' => 7,
            'owner_name' => 'Editor',
            'timezone' => 'Europe/London',
            'failure' => null,
        ], $event->toTableRecord());
    }

    public function test_publish_check_result_reports_errors_and_clean_results_independently(): void
    {
        $cleanError = new PublishCheckResult(
            identifier: 'seo',
            label: 'SEO',
            severity: PublishCheckSeverity::Error,
        );

        $dirtyWarning = new PublishCheckResult(
            identifier: 'links',
            label: 'Links',
            severity: PublishCheckSeverity::Warn,
            messages: ['Broken link found.'],
        );

        $this->assertTrue($cleanError->isError());
        $this->assertTrue($cleanError->isClean());
        $this->assertFalse($dirtyWarning->isError());
        $this->assertFalse($dirtyWarning->isClean());
    }

    public function test_entity_rollback_report_records_preview_and_execution_outcomes(): void
    {
        $report = new EntityRollbackReport(
            modelClass: 'App\\Models\\Page',
            entityUuid: 'page-uuid',
            targetVersionId: 15,
            restoredId: 22,
            replacedId: 24,
            noOp: false,
        );

        $this->assertSame('App\\Models\\Page', $report->modelClass);
        $this->assertSame('page-uuid', $report->entityUuid);
        $this->assertSame(15, $report->targetVersionId);
        $this->assertSame(22, $report->restoredId);
        $this->assertSame(24, $report->replacedId);
        $this->assertFalse($report->noOp);
    }

    #[DataProvider('mediaDiffProvider')]
    public function test_media_diff_result_reports_visual_diffs_when_either_media_side_exists(
        ?string $beforeUrl,
        ?string $afterUrl,
        bool $expected,
    ): void {
        $result = new MediaDiffResult(
            beforeUrl: $beforeUrl,
            afterUrl: $afterUrl,
            perceptualHashDelta: null,
            contentChanged: $expected,
        );

        $this->assertSame($expected, $result->hasVisualDiff());
    }
}
