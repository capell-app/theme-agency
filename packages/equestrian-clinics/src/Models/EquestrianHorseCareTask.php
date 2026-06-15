<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianCareTaskTypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $horse_profile_id
 * @property int|null $assigned_staff_member_id
 * @property EquestrianCareTaskTypeEnum $type
 * @property string $title
 * @property string|null $instructions
 * @property CarbonImmutable $due_at
 * @property CarbonImmutable|null $completed_at
 * @property int $billable_pence
 * @property array<string, mixed>|null $recurrence
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianHorseProfile $horseProfile
 * @property-read EquestrianStaffMember|null $assignedStaffMember
 */
final class EquestrianHorseCareTask extends Model
{
    protected $table = 'equestrian_horse_care_tasks';

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
    public function assignedStaffMember(): BelongsTo
    {
        return $this->belongsTo(EquestrianStaffMember::class, 'assigned_staff_member_id');
    }

    public function isComplete(): bool
    {
        return $this->completed_at instanceof CarbonImmutable;
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'billable_pence' => 'integer',
            'completed_at' => 'immutable_datetime',
            'due_at' => 'immutable_datetime',
            'meta' => 'json',
            'recurrence' => 'json',
            'type' => EquestrianCareTaskTypeEnum::class,
        ];
    }
}
