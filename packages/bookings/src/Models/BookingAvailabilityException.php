<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\BookingAvailabilityExceptionFactory;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int|null $capacity
 * @property CarbonImmutable $date
 * @property string|null $ends_at
 * @property int|null $location_id
 * @property int|null $service_id
 * @property int|null $staff_member_id
 * @property string|null $starts_at
 * @property BookingAvailabilityStatusEnum $status
 * @property string $timezone
 */
class BookingAvailabilityException extends Model
{
    /** @use HasFactory<BookingAvailabilityExceptionFactory> */
    use HasFactory;

    protected $table = 'booking_availability_exceptions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'capacity',
        'date',
        'ends_at',
        'location_id',
        'meta',
        'notes',
        'reason',
        'service_id',
        'staff_member_id',
        'starts_at',
        'status',
        'timezone',
    ];

    protected static string $factory = BookingAvailabilityExceptionFactory::class;

    /**
     * @return BelongsTo<BookingService, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(BookingService::class, 'service_id');
    }

    /**
     * @return BelongsTo<BookingStaffMember, $this>
     */
    public function staffMember(): BelongsTo
    {
        return $this->belongsTo(BookingStaffMember::class, 'staff_member_id');
    }

    /**
     * @return BelongsTo<BookingLocation, $this>
     */
    public function location(): BelongsTo
    {
        return $this->belongsTo(BookingLocation::class, 'location_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', BookingAvailabilityStatusEnum::Available->value);
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'date' => 'immutable_date',
            'meta' => 'json',
            'status' => BookingAvailabilityStatusEnum::class,
        ];
    }
}
