<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingGroupSessionStatusEnum;
use Capell\Core\Models\Site;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $capacity
 * @property int|null $location_id
 * @property int $service_id
 * @property int|null $site_id
 * @property int|null $staff_member_id
 * @property string|null $external_event_id
 * @property CarbonImmutable $ends_at
 * @property CarbonImmutable|null $offered_window_ends_at
 * @property CarbonImmutable|null $offered_window_starts_at
 * @property CarbonImmutable $starts_at
 * @property array<string, mixed>|null $meta
 * @property array<string, mixed>|null $social_attendance
 * @property BookingGroupSessionStatusEnum $status
 * @property string $title
 * @property-read BookingLocation|null $location
 */
class BookingGroupSession extends Model
{
    protected $table = 'booking_group_sessions';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'capacity',
        'description',
        'ends_at',
        'external_event_id',
        'fee_pence',
        'location_id',
        'meta',
        'offered_window_ends_at',
        'offered_window_starts_at',
        'service_id',
        'site_id',
        'social_attendance',
        'staff_member_id',
        'starts_at',
        'status',
        'title',
    ];

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

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
     * @return HasMany<AppointmentRequest, $this>
     */
    public function appointmentRequests(): HasMany
    {
        return $this->hasMany(AppointmentRequest::class, 'group_session_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'ends_at' => 'immutable_datetime',
            'fee_pence' => 'integer',
            'meta' => 'json',
            'offered_window_ends_at' => 'immutable_datetime',
            'offered_window_starts_at' => 'immutable_datetime',
            'social_attendance' => 'json',
            'starts_at' => 'immutable_datetime',
            'status' => BookingGroupSessionStatusEnum::class,
        ];
    }
}
