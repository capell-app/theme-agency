<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\BookingLocationFactory;
use Capell\Bookings\Enums\BookingLocationTypeEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property int $id
 * @property int $access_overhead_minutes
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $postal_code
 * @property string|null $service_area
 * @property string|null $timezone
 */
class BookingLocation extends Model
{
    /** @use HasFactory<BookingLocationFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'booking_locations';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'active',
        'access_overhead_minutes',
        'city',
        'country',
        'instructions',
        'latitude',
        'line1',
        'line2',
        'longitude',
        'meta',
        'name',
        'postal_code',
        'route_notes',
        'service_area',
        'settings',
        'state',
        'timezone',
        'type',
        'virtual_url',
    ];

    protected static string $factory = BookingLocationFactory::class;

    /**
     * @return HasMany<BookingAvailabilityWindow, $this>
     */
    public function availabilityWindows(): HasMany
    {
        return $this->hasMany(BookingAvailabilityWindow::class, 'location_id');
    }

    /**
     * @return HasMany<BookingAvailabilityException, $this>
     */
    public function availabilityExceptions(): HasMany
    {
        return $this->hasMany(BookingAvailabilityException::class, 'location_id');
    }

    /**
     * @return HasMany<AppointmentRequest, $this>
     */
    public function appointmentRequests(): HasMany
    {
        return $this->hasMany(AppointmentRequest::class, 'location_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'access_overhead_minutes' => 'integer',
            'latitude' => 'float',
            'longitude' => 'float',
            'meta' => 'json',
            'settings' => 'json',
            'type' => BookingLocationTypeEnum::class,
        ];
    }
}
