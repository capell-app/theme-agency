<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianHorseHealthRecordTypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $horse_profile_id
 * @property int|null $recorded_by_staff_member_id
 * @property EquestrianHorseHealthRecordTypeEnum $type
 * @property CarbonImmutable $occurred_at
 * @property CarbonImmutable|null $due_next_at
 * @property string|null $provider_name
 * @property string $summary
 * @property string|null $notes
 * @property int $billable_pence
 * @property array<int, array<string, mixed>>|null $documents
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianHorseProfile $horseProfile
 * @property-read EquestrianStaffMember|null $recordedByStaffMember
 */
final class EquestrianHorseHealthRecord extends Model
{
    protected $table = 'equestrian_horse_health_records';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianHorseProfile, $this>
     */
    public function horseProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianHorseProfile::class, 'horse_profile_id');
    }

    /**
     * @return BelongsTo<EquestrianStaffMember, $this>
     */
    public function recordedByStaffMember(): BelongsTo
    {
        return $this->belongsTo(EquestrianStaffMember::class, 'recorded_by_staff_member_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'billable_pence' => 'integer',
            'documents' => 'json',
            'due_next_at' => 'immutable_datetime',
            'meta' => 'json',
            'occurred_at' => 'immutable_datetime',
            'type' => EquestrianHorseHealthRecordTypeEnum::class,
        ];
    }
}
