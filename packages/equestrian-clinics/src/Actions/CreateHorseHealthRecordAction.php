<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianHorseHealthRecordTypeEnum;
use Capell\EquestrianClinics\Models\EquestrianHorseHealthRecord;
use Capell\EquestrianClinics\Models\EquestrianHorseProfile;
use Capell\EquestrianClinics\Models\EquestrianStaffMember;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianHorseHealthRecord run(EquestrianHorseProfile $horseProfile, EquestrianHorseHealthRecordTypeEnum $type, string $summary, CarbonImmutable $occurredAt, ?CarbonImmutable $dueNextAt = null, ?EquestrianStaffMember $recordedByStaffMember = null, ?string $providerName = null, ?string $notes = null, int $billablePence = 0, ?array<int, array<string, mixed>> $documents = null, ?array<string, mixed> $meta = null)
 */
final class CreateHorseHealthRecordAction
{
    use AsAction;

    /**
     * @param  array<int, array<string, mixed>>|null  $documents
     * @param  array<string, mixed>|null  $meta
     */
    public function handle(
        EquestrianHorseProfile $horseProfile,
        EquestrianHorseHealthRecordTypeEnum $type,
        string $summary,
        CarbonImmutable $occurredAt,
        ?CarbonImmutable $dueNextAt = null,
        ?EquestrianStaffMember $recordedByStaffMember = null,
        ?string $providerName = null,
        ?string $notes = null,
        int $billablePence = 0,
        ?array $documents = null,
        ?array $meta = null,
    ): EquestrianHorseHealthRecord {
        return EquestrianHorseHealthRecord::query()->create([
            'horse_profile_id' => $horseProfile->getKey(),
            'recorded_by_staff_member_id' => $recordedByStaffMember?->getKey(),
            'type' => $type,
            'occurred_at' => $occurredAt,
            'due_next_at' => $dueNextAt,
            'provider_name' => $providerName,
            'summary' => $summary,
            'notes' => $notes,
            'billable_pence' => $billablePence,
            'documents' => $documents,
            'meta' => $meta,
        ]);
    }
}
