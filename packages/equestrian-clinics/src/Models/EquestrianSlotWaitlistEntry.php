<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianWaitlistStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $tour_day_slot_id
 * @property int $rider_profile_id
 * @property int|null $horse_profile_id
 * @property EquestrianWaitlistStatusEnum $status
 * @property int $quoted_total_pence
 * @property CarbonImmutable|null $offered_at
 * @property CarbonImmutable|null $offer_expires_at
 * @property CarbonImmutable|null $claimed_at
 * @property CarbonImmutable|null $cancelled_at
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianTourDaySlot $slot
 * @property-read EquestrianRiderProfile $riderProfile
 * @property-read EquestrianHorseProfile|null $horseProfile
 */
final class EquestrianSlotWaitlistEntry extends Model
{
    protected $table = 'equestrian_slot_waitlist_entries';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianTourDaySlot, $this>
     */
    public function slot(): BelongsTo
    {
        return $this->belongsTo(EquestrianTourDaySlot::class, 'tour_day_slot_id');
    }

    /**
     * @return BelongsTo<EquestrianRiderProfile, $this>
     */
    public function riderProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianRiderProfile::class, 'rider_profile_id');
    }

    /**
     * @return BelongsTo<EquestrianHorseProfile, $this>
     */
    public function horseProfile(): BelongsTo
    {
        return $this->belongsTo(EquestrianHorseProfile::class, 'horse_profile_id');
    }

    public function offerIsClaimable(CarbonImmutable $now): bool
    {
        return $this->status === EquestrianWaitlistStatusEnum::Offered
            && $this->offer_expires_at instanceof CarbonImmutable
            && $this->offer_expires_at->greaterThan($now);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'immutable_datetime',
            'claimed_at' => 'immutable_datetime',
            'meta' => 'json',
            'offered_at' => 'immutable_datetime',
            'offer_expires_at' => 'immutable_datetime',
            'quoted_total_pence' => 'integer',
            'status' => EquestrianWaitlistStatusEnum::class,
        ];
    }
}
