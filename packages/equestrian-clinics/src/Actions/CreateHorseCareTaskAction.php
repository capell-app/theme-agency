<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianCareTaskTypeEnum;
use Capell\EquestrianClinics\Models\EquestrianHorseCareTask;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianStaffMember;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianHorseCareTask run(EquestrianHorseProfile $horseProfile, EquestrianCareTaskTypeEnum $type, string $title, CarbonImmutable $dueAt, ?EquestrianStaffMember $assignedStaffMember = null, ?string $instructions = null, int $billablePence = 0, ?array<string, mixed> $recurrence = null, ?array<string, mixed> $meta = null)
 */
final class CreateHorseCareTaskAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>|null  $recurrence
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        EquestrianHorseProfile $horseProfile,
        EquestrianCareTaskTypeEnum $type,
        string $title,
        CarbonImmutable $dueAt,
        ?EquestrianStaffMember $assignedStaffMember = null,
        ?string $instructions = null,
        int $billablePence = 0,
        ?array $recurrence = null,
        ?array $meta = null,
    ): EquestrianHorseCareTask {
        return EquestrianHorseCareTask::query()->create([
            'horse_profile_id' => $horseProfile->getKey(),
            'assigned_staff_member_id' => $assignedStaffMember?->getKey(),
            'type' => $type,
            'title' => $title,
            'instructions' => $instructions,
            'due_at' => $dueAt,
            'billable_pence' => $billablePence,
            'recurrence' => $recurrence,
            'meta' => $meta,
        ]);
    }
}
