<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\AppointmentRequestFactory;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\ConfirmationPolicyEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
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
 * @property ConfirmationPolicyEnum $confirmation_policy
 * @property string $customer_email
 * @property string $customer_name
 * @property CarbonImmutable|null $hold_expires_at
 * @property bool $is_time_pinned
 * @property array<string, mixed>|null $payload
 * @property CarbonImmutable|null $offered_window_ends_at
 * @property CarbonImmutable|null $offered_window_starts_at
 * @property array<string, mixed>|null $reminder_preferences
 * @property CarbonImmutable $requested_ends_at
 * @property CarbonImmutable $requested_starts_at
 * @property CarbonImmutable|null $series_occurrence_date
 * @property AppointmentRequestStatusEnum $status
 * @property string $timezone
 * @property-read LessonSeries|null $lessonSeries
 * @property-read BookingLocation|null $location
 * @property-read PortalAccount|null $portalAccount
 * @property-read BookingService $service
 * @property-read Site|null $site
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
        'confirmation_policy',
        'confirmation_token',
        'confirmed_at',
        'customer_email',
        'customer_name',
        'customer_phone',
        'hold_expires_at',
        'is_time_pinned',
        'lesson_series_id',
        'location_id',
        'meta',
        'notes',
        'offered_window_ends_at',
        'offered_window_starts_at',
        'payload',
        'portal_account_id',
        'reminder_preferences',
        'requested_at',
        'requested_ends_at',
        'requested_starts_at',
        'series_occurrence_date',
        'service_id',
        'site_id',
        'source',
        'staff_member_id',
        'status',
        'timezone',
    ];

    protected static string $factory = AppointmentRequestFactory::class;

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    /**
     * @return BelongsTo<PortalAccount, $this>
     */
    public function portalAccount(): BelongsTo
    {
        return $this->belongsTo(PortalAccount::class, 'portal_account_id');
    }

    /**
     * @return BelongsTo<LessonSeries, $this>
     */
    public function lessonSeries(): BelongsTo
    {
        return $this->belongsTo(LessonSeries::class, 'lesson_series_id');
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
            'confirmation_policy' => ConfirmationPolicyEnum::class,
            'confirmed_at' => 'immutable_datetime',
            'hold_expires_at' => 'immutable_datetime',
            'is_time_pinned' => 'boolean',
            'meta' => 'json',
            'offered_window_ends_at' => 'immutable_datetime',
            'offered_window_starts_at' => 'immutable_datetime',
            'payload' => 'json',
            'reminder_preferences' => 'json',
            'requested_at' => 'immutable_datetime',
            'requested_ends_at' => 'immutable_datetime',
            'requested_starts_at' => 'immutable_datetime',
            'series_occurrence_date' => 'immutable_date',
            'status' => AppointmentRequestStatusEnum::class,
        ];
    }
}
