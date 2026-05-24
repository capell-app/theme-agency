<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Enums\PublishingRevisionEventEnum;
use Capell\PublishingStudio\Models\PublishingRevision;
use Capell\PublishingStudio\Models\Version;
use Capell\PublishingStudio\Models\Workspace;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

final class RecordPublishingRevisionAction
{
    use AsAction;

    /**
     * @return array<array-key, mixed>
     */
    public static function payloadFor(Model $record): array
    {
        return $record->getAttributes();
    }

    /**
     * @param  array<array-key, mixed>  $afterPayload
     * @param  array<array-key, mixed>  $beforePayload
     */
    public function handle(
        string $revisionableType,
        int $revisionableId,
        ?string $revisionableUuid,
        PublishingRevisionEventEnum $eventType,
        ?array $beforePayload,
        ?array $afterPayload,
        ?Workspace $workspace = null,
        ?Version $version = null,
        ?Authenticatable $actor = null,
        ?string $notes = null,
    ): PublishingRevision {
        $nextVersion = $this->nextVersion($revisionableType, $revisionableId, $revisionableUuid);

        return PublishingRevision::query()->create([
            'uuid' => (string) Str::uuid(),
            'revisionable_type' => $revisionableType,
            'revisionable_id' => $revisionableId,
            'revisionable_uuid' => $revisionableUuid,
            'workspace_id' => $workspace?->getKey(),
            'version_id' => $version?->getKey(),
            'version' => $nextVersion,
            'event_type' => $eventType,
            'before_payload' => $beforePayload,
            'after_payload' => $afterPayload,
            'actor_type' => $actor?->getMorphClass(),
            'actor_id' => $actor?->getKey(),
            'notes' => $notes,
        ]);
    }

    private function nextVersion(string $revisionableType, int $revisionableId, ?string $revisionableUuid): int
    {
        $query = PublishingRevision::query()
            ->where('revisionable_type', $revisionableType)
            ->when(
                $revisionableUuid !== null && $revisionableUuid !== '',
                fn (Builder $query): Builder => $query->where('revisionable_uuid', $revisionableUuid),
                fn (Builder $query): Builder => $query->where('revisionable_id', $revisionableId),
            );

        return ((int) $query->max('version')) + 1;
    }
}
