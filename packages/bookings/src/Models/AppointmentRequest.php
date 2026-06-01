<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\AppointmentRequestFactory;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property string $calendar_uid
 * @property CarbonImmutable|null $confirmed_at
 * @property string $customer_email
 * @property string $customer_name
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $reminder_preferences
 * @property CarbonImmutable $requested_ends_at
 * @property CarbonImmutable $requested_starts_at
 * @property AppointmentRequestStatusEnum $status
 * @property string $timezone
 * @property-read BookingLocation|null $location
 * @property-read BookingService $service
 * @property-read BookingStaffMember|null $staffMember
 */
class AppointmentRequest extends Model
{
    /** @use HasFactory<AppointmentRequestFactory> */
    use HasFactory;

    protected $table = 'appointment_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'calendar_uid',
        'cancelled_at',
        'completed_at',
        'confirmation_token',
        'confirmed_at',
        'customer_email',
        'customer_name',
        'customer_phone',
        'location_id',
        'meta',
        'notes',
        'payload',
        'reminder_preferences',
        'requested_at',
        'requested_ends_at',
        'requested_starts_at',
        'service_id',
        'source',
        'staff_member_id',
        'status',
        'timezone',
    ];

    protected static string $factory = AppointmentRequestFactory::class;

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
     * @return HasMany<AppointmentAuditLog, $this>
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AppointmentAuditLog::class, 'appointment_request_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('requested_starts_at', '>=', CarbonImmutable::now());
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'cancelled_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'meta' => 'json',
            'payload' => 'json',
            'reminder_preferences' => 'json',
            'requested_at' => 'immutable_datetime',
            'requested_ends_at' => 'immutable_datetime',
            'requested_starts_at' => 'immutable_datetime',
            'status' => AppointmentRequestStatusEnum::class,
        ];
    }
}
