<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\BookingAvailabilityWindowFactory;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $capacity
 * @property int $day_of_week
 * @property CarbonImmutable|null $effective_from
 * @property CarbonImmutable|null $effective_until
 * @property string $ends_at
 * @property int|null $location_id
 * @property int|null $service_id
 * @property int|null $staff_member_id
 * @property string $starts_at
 * @property BookingAvailabilityStatusEnum $status
 * @property string $timezone
 */
class BookingAvailabilityWindow extends Model
{
    /** @use HasFactory<BookingAvailabilityWindowFactory> */
    use HasFactory;

    protected $table = 'booking_availability_windows';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'capacity',
        'day_of_week',
        'effective_from',
        'effective_until',
        'ends_at',
        'location_id',
        'meta',
        'notes',
        'service_id',
        'staff_member_id',
        'starts_at',
        'status',
        'timezone',
    ];

    protected static string $factory = BookingAvailabilityWindowFactory::class;

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
            'day_of_week' => 'integer',
            'effective_from' => 'immutable_date',
            'effective_until' => 'immutable_date',
            'meta' => 'json',
            'status' => BookingAvailabilityStatusEnum::class,
        ];
    }
}
