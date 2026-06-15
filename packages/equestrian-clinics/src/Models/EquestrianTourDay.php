<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Models;

use Capell\EquestrianClinics\Enums\EquestrianTourDayStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int $venue_id
 * @property string $title
 * @property EquestrianTourDayStatusEnum $status
 * @property CarbonImmutable $starts_at
 * @property CarbonImmutable $ends_at
 * @property int $booking_lock_hours
 * @property int $cancellation_refund_hours
 * @property int $minimum_paid_attendees
 * @property int $minimum_revenue_pence
 * @property bool $is_public
 * @property int|null $event_occurrence_id
 * @property array<string, mixed>|null $meta
 * @property-read EquestrianVenue $venue
 * @property-read Collection<int, EquestrianTourDaySlot> $slots
 */
final class EquestrianTourDay extends Model
{
    protected $table = 'equestrian_tour_days';

    protected $guarded = [];

    /**
     * @return BelongsTo<EquestrianVenue, $this>
     */
    public function venue(): BelongsTo
    {
        return $this->belongsTo(EquestrianVenue::class, 'venue_id');
    }

    /**
     * @return HasMany<EquestrianTourDaySlot, $this>
     */
    public function slots(): HasMany
    {
        return $this->hasMany(EquestrianTourDaySlot::class, 'tour_day_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'booking_lock_hours' => 'integer',
            'cancellation_refund_hours' => 'integer',
            'ends_at' => 'immutable_datetime',
            'is_public' => 'boolean',
            'meta' => 'json',
            'minimum_paid_attendees' => 'integer',
            'minimum_revenue_pence' => 'integer',
            'starts_at' => 'immutable_datetime',
            'status' => EquestrianTourDayStatusEnum::class,
        ];
    }
}
