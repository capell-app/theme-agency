<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Exceptions\EmbargoActiveException;
use Capell\PublishingStudio\Exceptions\ReleaseWindowClosedException;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\Publisher;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;
use Throwable;

final class ExecuteSchedulerEventAction
{
    use AsAction;

    public function handle(SchedulerEvent $event): void
    {
        if ($event->state === SchedulerEventStateEnum::Executed) {
            return;
        }

        $claimed = DB::transaction(function () use ($event): bool {
            $lockedEvent = SchedulerEvent::query()
                ->whereKey($event->getKey())
                ->lockForUpdate()
                ->first();

            if (! $lockedEvent instanceof SchedulerEvent || ! $this->canClaim($lockedEvent)) {
                return false;
            }

            $lockedEvent->state = SchedulerEventStateEnum::Executing;
            $lockedEvent->claimed_at = CarbonImmutable::now();
            $lockedEvent->last_attempted_at = CarbonImmutable::now();
            $lockedEvent->save();

            $event->setRawAttributes($lockedEvent->getAttributes(), true);

            return true;
        });

        if (! $claimed) {
            return;
        }

        try {
            match ($event->event_type) {
                SchedulerEventTypeEnum::Publish => $this->publish($event),
                SchedulerEventTypeEnum::Unpublish => $this->unpublish($event),
                SchedulerEventTypeEnum::ReviewReminder => $this->sendReminder($event),
                SchedulerEventTypeEnum::Embargo => $event->markExecuted(),
            };
        } catch (EmbargoActiveException) {
            $event->markSkipped(SchedulerEventStateEnum::SkippedEmbargo, 'embargo_active');
        } catch (ReleaseWindowClosedException $releaseWindowClosed) {
            $nextOpensAt = $releaseWindowClosed->nextOpensAt;
            $originallyScheduledFor = $event->scheduled_for;

            $event->markSkippedUntilReleaseWindowOpens('release_window_closed', $nextOpensAt);

            Log::warning('Scheduled publish deferred: release window closed.', [
                'scheduler_event_id' => $event->getKey(),
                'workspace_id' => $event->workspace_id,
                'originally_scheduled_for' => $originallyScheduledFor->toDateTimeString(),
                'next_opens_at' => $nextOpensAt?->toDateTimeString(),
            ]);
        } catch (Throwable $failure) {
            $event->markFailed($failure);
            report($failure);
        }
    }

    private function canClaim(SchedulerEvent $event): bool
    {
        if (in_array($event->state, [
            SchedulerEventStateEnum::Scheduled,
            SchedulerEventStateEnum::Failed,
            SchedulerEventStateEnum::SkippedEmbargo,
            SchedulerEventStateEnum::SkippedReleaseWindow,
        ], true)) {
            return true;
        }

        return $event->state === SchedulerEventStateEnum::Executing
            && $event->claimed_at !== null
            && $event->claimed_at->lessThanOrEqualTo(now()->subMinutes(15));
    }

    private function publish(SchedulerEvent $event): void
    {
        $workspace = $event->workspace;

        if (! $workspace instanceof Workspace) {
            $event->markSkipped(SchedulerEventStateEnum::SkippedStale, 'workspace_missing');

            return;
        }

        if ($this->isStale($event, $workspace)) {
            $event->markSkipped(SchedulerEventStateEnum::SkippedStale, 'schedule_superseded');

            return;
        }

        (new Publisher)->publish($workspace);
        $event->markExecuted();
    }

    private function unpublish(SchedulerEvent $event): void
    {
        $workspace = $event->workspace;

        if (! $workspace instanceof Workspace) {
            $event->markSkipped(SchedulerEventStateEnum::SkippedStale, 'workspace_missing');

            return;
        }

        if ($this->isStale($event, $workspace)) {
            $event->markSkipped(SchedulerEventStateEnum::SkippedStale, 'schedule_superseded');

            return;
        }

        ExpireWorkspacePublicVisibilityAction::run($workspace, $event->scheduled_for);
        $event->markExecuted();
    }

    private function sendReminder(SchedulerEvent $event): void
    {
        SendWorkspaceReviewReminderAction::run($event);
        $event->markExecuted();
    }

    private function isStale(SchedulerEvent $event, Workspace $workspace): bool
    {
        return $workspace->updated_at !== null
            && $workspace->updated_at->getTimestamp() > $event->schedule_version;
    }
}
