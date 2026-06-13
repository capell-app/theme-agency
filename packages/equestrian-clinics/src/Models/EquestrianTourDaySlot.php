<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianSlotArchetypeEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $tour_day_id
 * @property string $title
 * @property EquestrianSlotArchetypeEnum $archetype
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property int $capacity_min
 * @property int $capacity_max
 * @property int $booked_count
 * @property int $waitlist_count
 * @property string|null $skill_tier
 * @property int $price_pence
 * @property int|null $deposit_pence
 * @property int|null $booking_service_id
 * @property int|null $booking_group_session_id
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianTourDay $tourDay
 * @property-read Collection<int, EquestrianFacilityBooking> $facilityBookings
 */
final class EquestrianTourDaySlot extends Model
{
    protected $table = 'equestrian_tour_day_slots';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianTourDay, $this>
     */
    public function tourDay(): BelongsTo
    {
        return $this->belongsTo(EquestrianTourDay::class, 'tour_day_id');
    }

    /**
     * @return HasMany<EquestrianFacilityBooking, $this>
     */
    public function facilityBookings(): HasMany
    {
        return $this->hasMany(EquestrianFacilityBooking::class, 'tour_day_slot_id');
    }

    public function remainingCapacity(): int
    {
        return max(0, $this->capacity_max - $this->booked_count);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'archetype' => EquestrianSlotArchetypeEnum::class,
            'booked_count' => 'integer',
            'capacity_max' => 'integer',
            'capacity_min' => 'integer',
            'deposit_pence' => 'integer',
            'ends_at' => 'immutable_datetime',
            'meta' => 'json',
            'price_pence' => 'integer',
            'starts_at' => 'immutable_datetime',
            'waitlist_count' => 'integer',
        ];
    }
}
