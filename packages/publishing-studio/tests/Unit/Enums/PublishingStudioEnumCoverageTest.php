<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Tests\Unit\Enums;

use Capell\PublishingStudio\Enums\ResourceEnum;
use Capell\PublishingStudio\Enums\ReviewDecisionEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Enums\WorkspaceTransitionEnum;
use Capell\PublishingStudio\Filament\Resources\PreviewLinks\PreviewLinkResource;
use Capell\PublishingStudio\Filament\Resources\PublishingStudio\WorkspaceResource;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/** @extends TestCase<MockObject> */
final class PublishingStudioEnumCoverageTest extends TestCase
{
    public function test_resource_enum_points_at_the_filament_resources_it_exposes(): void
    {
        $this->assertSame(PreviewLinkResource::class, ResourceEnum::PreviewLink->value);
        $this->assertSame(WorkspaceResource::class, ResourceEnum::Workspace->value);
    }

    public function test_review_decision_enum_uses_persisted_decision_values(): void
    {
        $this->assertSame([
            'approved',
            'rejected',
        ], array_map(
            static fn (ReviewDecisionEnum $decision): string => $decision->value,
            ReviewDecisionEnum::cases(),
        ));
    }

    public function test_scheduler_event_type_enum_maps_every_case_to_a_filament_colour(): void
    {
        $this->assertSame([
            'publish' => 'success',
            'unpublish' => 'danger',
            'embargo' => 'warning',
            'review_reminder' => 'info',
        ], [
            SchedulerEventTypeEnum::Publish->value => SchedulerEventTypeEnum::Publish->getColor(),
            SchedulerEventTypeEnum::Unpublish->value => SchedulerEventTypeEnum::Unpublish->getColor(),
            SchedulerEventTypeEnum::Embargo->value => SchedulerEventTypeEnum::Embargo->getColor(),
            SchedulerEventTypeEnum::ReviewReminder->value => SchedulerEventTypeEnum::ReviewReminder->getColor(),
        ]);
    }

    public function test_workspace_transition_enum_exposes_stable_audit_event_values(): void
    {
        $this->assertSame([
            'submitted',
            'approved',
            'rejected',
            'scheduled',
            'unscheduled',
            'published',
            'abandoned',
            'changes_requested',
        ], array_map(
            static fn (WorkspaceTransitionEnum $transition): string => $transition->value,
            WorkspaceTransitionEnum::cases(),
        ));
    }
}
