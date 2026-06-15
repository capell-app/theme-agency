<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Models\EquestrianHorseCareTask;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianHorseCareTask run(EquestrianHorseCareTask $task, ?CarbonImmutable $completedAt = null, ?string $completionNote = null)
 */
final class CompleteHorseCareTaskAction
{
    use AsAction;

    public function handle(
        EquestrianHorseCareTask $task,
        ?CarbonImmutable $completedAt = null,
        ?string $completionNote = null,
    ): EquestrianHorseCareTask {
        $completedAt ??= CarbonImmutable::now();

        $task->forceFill([
            'completed_at' => $completedAt,
            'meta' => [
                ...($task->meta ?? []),
                'completion_note' => $completionNote,
            ],
        ])->save();

        return $task->refresh();
    }
}
