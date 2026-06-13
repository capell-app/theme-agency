<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\BookingServiceFactory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property bool $active
 * @property int $buffer_after_minutes
 * @property int $buffer_before_minutes
 * @property bool $confirmation_required
 * @property CarbonImmutable|null $created_at
 * @property int $duration_minutes
 * @property int $lead_time_minutes
 * @property int|null $max_future_days
 * @property string $name
 */
class BookingService extends Model
{
    /** @use HasFactory<BookingServiceFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'booking_services';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'active',
        'buffer_after_minutes',
        'buffer_before_minutes',
        'color',
        'confirmation_required',
        'description',
        'duration_minutes',
        'instructions',
        'lead_time_minutes',
        'max_future_days',
        'meta',
        'name',
        'settings',
    ];

    protected static string $factory = BookingServiceFactory::class;

    /**
     * @return HasMany<BookingAvailabilityWindow, $this>
     */
    public function availabilityWindows(): HasMany
    {
        return $this->hasMany(BookingAvailabilityWindow::class, 'service_id');
    }

    /**
     * @return HasMany<BookingAvailabilityException, $this>
     */
    public function availabilityExceptions(): HasMany
    {
        return $this->hasMany(BookingAvailabilityException::class, 'service_id');
    }

    /**
     * @return HasMany<AppointmentRequest, $this>
     */
    public function appointmentRequests(): HasMany
    {
        return $this->hasMany(AppointmentRequest::class, 'service_id');
    }

    /**
     * @return HasMany<BookingGroupSession, $this>
     */
    public function groupSessions(): HasMany
    {
        return $this->hasMany(BookingGroupSession::class, 'service_id');
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
            'buffer_after_minutes' => 'integer',
            'buffer_before_minutes' => 'integer',
            'confirmation_required' => 'boolean',
            'duration_minutes' => 'integer',
            'lead_time_minutes' => 'integer',
            'max_future_days' => 'integer',
            'meta' => 'json',
            'settings' => 'json',
        ];
    }
}
