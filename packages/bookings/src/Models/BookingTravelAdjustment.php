<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property bool $active
 * @property CarbonImmutable|null $effective_from
 * @property CarbonImmutable|null $effective_until
 * @property int $extra_minutes
 */
class BookingTravelAdjustment extends Model
{
    protected $table = 'booking_travel_adjustments';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'active',
        'destination_location_id',
        'effective_from',
        'effective_until',
        'extra_minutes',
        'meta',
        'origin_location_id',
        'reason',
    ];

    /**
     * @return BelongsTo<BookingLocation, $this>
     */
    public function originLocation(): BelongsTo
    {
        return $this->belongsTo(BookingLocation::class, 'origin_location_id');
    }

    /**
     * @return BelongsTo<BookingLocation, $this>
     */
    public function destinationLocation(): BelongsTo
    {
        return $this->belongsTo(BookingLocation::class, 'destination_location_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'effective_from' => 'immutable_datetime',
            'effective_until' => 'immutable_datetime',
            'extra_minutes' => 'integer',
            'meta' => 'json',
        ];
    }
}
