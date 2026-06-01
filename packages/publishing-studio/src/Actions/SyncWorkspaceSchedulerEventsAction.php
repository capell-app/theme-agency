<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\Core\Models\Page;
use Capell\PublishingStudio\Data\WorkspaceSchedulerMetadataData;
use Capell\PublishingStudio\Enums\SchedulerEventStateEnum;
use Capell\PublishingStudio\Enums\SchedulerEventTypeEnum;
use Capell\PublishingStudio\Models\SchedulerEvent;
use Capell\PublishingStudio\Models\Workspace;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Lorisleiva\Actions\Concerns\AsAction;

final class SyncWorkspaceSchedulerEventsAction
{
    use AsAction;

    public function handle(
        Workspace $workspace,
        ?WorkspaceSchedulerMetadataData $metadata = null,
        ?Authenticatable $actor = null,
    ): void {
        $metadata ??= new WorkspaceSchedulerMetadataData(
            publishAt: $workspace->publish_at,
            unpublishAt: $workspace->unpublish_at,
            embargoUntil: $workspace->embargo_until,
            reviewReminderAt: $workspace->review_reminder_at,
            displayTimezone: config('app.timezone', 'UTC'),
        );

        $this->sync($workspace, SchedulerEventTypeEnum::Publish, $metadata->publishAt, $metadata->displayTimezone, $actor);
        $this->sync($workspace, SchedulerEventTypeEnum::Unpublish, $metadata->unpublishAt, $metadata->displayTimezone, $actor);
        $this->sync($workspace, SchedulerEventTypeEnum::Embargo, $metadata->embargoUntil, $metadata->displayTimezone, $actor);
        $this->sync($workspace, SchedulerEventTypeEnum::ReviewReminder, $metadata->reviewReminderAt, $metadata->displayTimezone, $actor);
    }

    private function sync(
        Workspace $workspace,
        SchedulerEventTypeEnum $eventType,
        ?CarbonImmutable $scheduledFor,
        string $displayTimezone,
        ?Authenticatable $actor,
    ): void {
        $sourceType = $workspace->getMorphClass();

        SchedulerEvent::query()
            ->where('source_type', $sourceType)
            ->where('source_id', $workspace->id)
            ->where('event_type', $eventType->value)
            ->whereIn('state', [SchedulerEventStateEnum::Scheduled->value, SchedulerEventStateEnum::Failed->value])
            ->when(
                $scheduledFor instanceof CarbonImmutable,
                fn (Builder $query): Builder => $query->where('scheduled_for', '!=', $scheduledFor),
            )
            ->update([
                'state' => SchedulerEventStateEnum::Cancelled->value,
                'skipped_reason' => 'superseded',
                'claimed_at' => null,
                'updated_at' => now(),
            ]);

        if (! $scheduledFor instanceof CarbonImmutable) {
            return;
        }

        $event = SchedulerEvent::query()
            ->where('idempotency_key', $this->idempotencyKey($workspace, $eventType, $scheduledFor))
            ->first();

        if ($event instanceof SchedulerEvent && $event->state !== SchedulerEventStateEnum::Scheduled && $event->state !== SchedulerEventStateEnum::Failed) {
            return;
        }

        $event ??= new SchedulerEvent([
            'idempotency_key' => $this->idempotencyKey($workspace, $eventType, $scheduledFor),
        ]);
        $isNewEvent = ! $event->exists;

        $event->fill([
            'event_type' => $eventType,
            'state' => SchedulerEventStateEnum::Scheduled,
            'source_type' => $sourceType,
            'source_id' => $workspace->id,
            'workspace_id' => $workspace->id,
            'site_id' => $this->siteId($workspace),
            'owner_type' => $isNewEvent ? $actor?->getMorphClass() : $event->owner_type,
            'owner_id' => $isNewEvent ? $actor?->getKey() : $event->owner_id,
            'scheduled_for' => $scheduledFor,
            'display_timezone' => $displayTimezone,
            'schedule_version' => $workspace->updated_at?->getTimestamp() ?? now()->getTimestamp(),
            'actor_type' => $actor?->getMorphClass() ?? $event->actor_type,
            'actor_id' => $actor?->getKey() ?? $event->actor_id,
            'claimed_at' => null,
            'last_failure_class' => null,
            'last_failure_message' => null,
            'skipped_reason' => null,
            'metadata' => [
                'workspace_status' => $workspace->status->value,
            ],
        ]);
        $event->save();
    }

    private function idempotencyKey(Workspace $workspace, SchedulerEventTypeEnum $eventType, CarbonImmutable $scheduledFor): string
    {
        return implode(':', ['workspace', (string) $workspace->id, $eventType->value, (string) $scheduledFor->getTimestamp()]);
    }

    private function siteId(Workspace $workspace): ?int
    {
        $page = Page::query()
            ->withoutGlobalScopes()
            ->where('workspace_id', $workspace->id)
            ->whereNotNull('site_id')
            ->first(['site_id']);

        return $page instanceof Page && is_numeric($page->site_id) ? (int) $page->site_id : null;
    }
}
