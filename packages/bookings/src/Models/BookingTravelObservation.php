<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property float $distance_miles
 * @property int $duration_minutes
 * @property int|null $origin_location_id
 * @property int|null $destination_location_id
 */
class BookingTravelObservation extends Model
{
    protected $table = 'booking_travel_observations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'destination_location_id',
        'distance_miles',
        'duration_minutes',
        'meta',
        'observed_at',
        'origin_location_id',
        'source',
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
            'distance_miles' => 'float',
            'duration_minutes' => 'integer',
            'meta' => 'json',
            'observed_at' => 'immutable_datetime',
        ];
    }
}
