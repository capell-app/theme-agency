<?php

declare(strict_types=1);

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Actions\BuildEditorialCalendarEventsAction;
use Capell\PublishingStudio\Actions\Dashboard\BuildSiteStatsAction;
use Capell\PublishingStudio\Actions\Dashboard\BuildWorkspaceActivityAction;
use Capell\PublishingStudio\Actions\Dashboard\BuildWorkspaceMergeHistoryAction;
use Capell\PublishingStudio\Actions\DashboardReports\BuildContentSchedulerEventsAction;
use Capell\PublishingStudio\Actions\Workflow\BuildPublishingWorkflowCommandCenterAction;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Models\WorkspaceReviewAssignment;
use Capell\Tests\Support\Concerns\CreatesAdminUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(CreatesAdminUser::class);

afterEach(function (): void {
    CarbonImmutable::setTestNow();
});

it('keeps Publishing Studio dashboard and calendar actions within the admin query budget', function (): void {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 09:00:00', 'UTC'));

    $actor = $this->createUserWithRole('super_admin');
    $openWorkspace = Workspace::factory()->open()->create(['created_by' => $actor->getAuthIdentifier()]);
    $reviewWorkspace = Workspace::factory()->inReview()->create();
    $scheduledWorkspace = Workspace::factory()->scheduled('2026-06-10 09:00:00')->create();
    $publishedWorkspaces = Workspace::factory()
        ->count(5)
        ->published()
        ->create(['created_by' => $actor->getAuthIdentifier()]);

    WorkspaceReviewAssignment::factory()->create([
        'workspace_id' => $reviewWorkspace->getKey(),
        'reviewer_type' => $actor->getMorphClass(),
        'reviewer_id' => $actor->getAuthIdentifier(),
        'decision' => null,
    ]);

    Page::factory()->count(6)->create([
        'workspace_id' => 0,
        'visible_from' => CarbonImmutable::parse('2026-06-09 09:00:00', 'UTC'),
        'visible_until' => CarbonImmutable::parse('2026-06-20 09:00:00', 'UTC'),
    ]);
    Page::factory()->count(3)->create(['workspace_id' => $openWorkspace->getKey()]);

    foreach ($publishedWorkspaces as $publishedWorkspace) {
        Page::factory()->create(['workspace_id' => $publishedWorkspace->getKey()]);
        Version::query()->create([
            'uuid' => (string) Str::uuid(),
            'number' => publishingStudioIntegerValue(Version::query()->max('number')) + 1,
            'name' => 'Published ' . $publishedWorkspace->getKey(),
            'is_live' => false,
            'manifest' => [],
            'source_workspace_id' => $publishedWorkspace->getKey(),
            'published_at' => CarbonImmutable::now()->subDays(publishingStudioIntegerModelKey($publishedWorkspace) % 5),
        ]);
    }

    SchedulerEvent::query()->create([
        'event_type' => SchedulerEventTypeEnum::Publish,
        'state' => SchedulerEventStateEnum::Scheduled,
        'source_type' => $scheduledWorkspace->getMorphClass(),
        'source_id' => $scheduledWorkspace->getKey(),
        'workspace_id' => $scheduledWorkspace->getKey(),
        'scheduled_for' => CarbonImmutable::parse('2026-06-10 09:00:00', 'UTC'),
        'idempotency_key' => 'budget-publish-' . $scheduledWorkspace->getKey(),
    ]);

    $budget = publishingStudioAdminQueryBudget();
    $queryCounts = [
        'workflow command center' => countPublishingStudioQueries(
            fn (): array => BuildPublishingWorkflowCommandCenterAction::run($actor),
        ),
        'site stats dashboard' => countPublishingStudioQueries(
            fn (): object => BuildSiteStatsAction::run(),
        ),
        'workspace activity dashboard' => countPublishingStudioQueries(
            fn (): object => BuildWorkspaceActivityAction::run($actor),
        ),
        'workspace merge history dashboard' => countPublishingStudioQueries(
            fn (): object => BuildWorkspaceMergeHistoryAction::run(),
        ),
        'content scheduler events' => countPublishingStudioQueries(
            fn (): object => BuildContentSchedulerEventsAction::run(),
        ),
        'editorial calendar events' => countPublishingStudioQueries(
            fn (): object => BuildEditorialCalendarEventsAction::run(),
        ),
    ];

    foreach ($queryCounts as $actionName => $queryCount) {
        expect($queryCount)
            ->toBeLessThanOrEqual($budget, sprintf('%s used %d queries, budget is %d.', $actionName, $queryCount, $budget));
    }
});

function publishingStudioAdminQueryBudget(): int
{
    $manifest = json_decode(
        file_get_contents(dirname(__DIR__, 3) . '/capell.json') ?: '{}',
        true,
        flags: JSON_THROW_ON_ERROR,
    );

    return publishingStudioIntegerValue(data_get($manifest, 'performance.adminQueryBudget', 40), 40);
}

function countPublishingStudioQueries(Closure $callback): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    try {
        $callback();

        return count(DB::getQueryLog());
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }
}

function publishingStudioIntegerValue(mixed $value, int $fallback = 0): int
{
    return is_numeric($value) ? (int) $value : $fallback;
}

function publishingStudioIntegerModelKey(Model $model): int
{
    return publishingStudioIntegerValue($model->getKey());
}
