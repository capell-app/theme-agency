<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Models\PublishingRevision;
use Illuminate\Database\Eloquent\Model;
use Lorisleiva\Actions\Concerns\AsAction;

final class ComparePublishingRevisionAction
{
    use AsAction;

    /**
     * @return array{
     *     model: class-string<Model>,
     *     uuid: ?string,
     *     revision_id: int,
     *     revision_version: int,
     *     kind: string,
     *     changes: array<string, array{before: mixed, after: mixed}>
     * }
     */
    public function handle(PublishingRevision $revision): array
    {
        $before = $revision->before_payload ?? [];
        $after = $revision->after_payload ?? [];
        $keys = array_values(array_unique(array_merge(array_keys($before), array_keys($after))));
        sort($keys);

        $changes = [];

        foreach ($keys as $key) {
            if (in_array($key, ['created_at', 'updated_at'], true)) {
                continue;
            }

            $beforeValue = $before[$key] ?? null;
            $afterValue = $after[$key] ?? null;

            if ($beforeValue === $afterValue) {
                continue;
            }

            $changes[(string) $key] = [
                'before' => $beforeValue,
                'after' => $afterValue,
            ];
        }

        $modelClass = is_string($revision->revisionable_type) && is_subclass_of($revision->revisionable_type, Model::class)
            ? $revision->revisionable_type
            : Model::class;

        return [
            'model' => $modelClass,
            'uuid' => $revision->revisionable_uuid,
            'revision_id' => (int) $revision->getKey(),
            'revision_version' => $revision->version,
            'kind' => $revision->event_type->value,
            'changes' => $changes,
        ];
    }
}
