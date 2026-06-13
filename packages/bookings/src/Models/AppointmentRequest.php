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
 * @property int $id
 * @property string $calendar_uid
 * @property int|null $location_id
 * @property int|null $service_id
 * @property int|null $staff_member_id
 * @property CarbonImmutable|null $completed_at
 * @property string|null $customer_phone
 * @property CarbonImmutable|null $confirmed_at
 * @property ConfirmationPolicyEnum $confirmation_policy
 * @property string $customer_email
 * @property string $customer_name
 * @property int|null $portal_account_id
 * @property int|null $group_session_id
 * @property CarbonImmutable|null $hold_expires_at
 * @property bool $is_time_pinned
 * @property int|null $fuel_allowance_pence
 * @property CarbonImmutable|null $fuel_acknowledged_at
 * @property array<string, mixed>|null $payload
 * @property string|null $payment_checkout_session_id
 * @property CarbonImmutable|null $payment_confirmed_at
 * @property string|null $payment_provider_reference
 * @property int|null $payment_required_amount_pence
 * @property CarbonImmutable|null $offered_window_ends_at
 * @property CarbonImmutable|null $offered_window_starts_at
 * @property array<string, mixed>|null $reminder_preferences
 * @property CarbonImmutable $requested_ends_at
 * @property CarbonImmutable $requested_starts_at
 * @property CarbonImmutable|null $series_occurrence_date
 * @property AppointmentRequestStatusEnum $status
 * @property int|null $site_id
 * @property string $timezone
 * @property float|null $travel_distance_miles
 * @property int|null $travel_duration_minutes
 * @property-read BookingGroupSession|null $groupSession
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
        'attendance_status',
        'attended_at',
        'fuel_acknowledged_at',
        'fuel_allowance_pence',
        'group_session_id',
        'hold_expires_at',
        'is_time_pinned',
        'lesson_series_id',
        'location_id',
        'meta',
        'notes',
        'offered_window_ends_at',
        'offered_window_starts_at',
        'payload',
        'payment_checkout_session_id',
        'payment_confirmed_at',
        'payment_provider_reference',
        'payment_required_amount_pence',
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
        'travel_distance_miles',
        'travel_duration_minutes',
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
     * @return BelongsTo<BookingGroupSession, $this>
     */
    public function groupSession(): BelongsTo
    {
        return $this->belongsTo(BookingGroupSession::class, 'group_session_id');
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
     * @return HasMany<LessonNote, $this>
     */
    public function lessonNotes(): HasMany
    {
        return $this->hasMany(LessonNote::class, 'appointment_request_id');
    }

    /**
     * @return HasMany<BookingMessageLog, $this>
     */
    public function messageLogs(): HasMany
    {
        return $this->hasMany(BookingMessageLog::class, 'appointment_request_id');
    }

    /**
     * @return HasMany<BookingReviewRequest, $this>
     */
    public function reviewRequests(): HasMany
    {
        return $this->hasMany(BookingReviewRequest::class, 'appointment_request_id');
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
            'attended_at' => 'immutable_datetime',
            'cancelled_at' => 'immutable_datetime',
            'completed_at' => 'immutable_datetime',
            'confirmation_policy' => ConfirmationPolicyEnum::class,
            'confirmed_at' => 'immutable_datetime',
            'fuel_acknowledged_at' => 'immutable_datetime',
            'fuel_allowance_pence' => 'integer',
            'hold_expires_at' => 'immutable_datetime',
            'is_time_pinned' => 'boolean',
            'meta' => 'json',
            'offered_window_ends_at' => 'immutable_datetime',
            'offered_window_starts_at' => 'immutable_datetime',
            'payload' => 'json',
            'payment_confirmed_at' => 'immutable_datetime',
            'payment_required_amount_pence' => 'integer',
            'reminder_preferences' => 'json',
            'requested_at' => 'immutable_datetime',
            'requested_ends_at' => 'immutable_datetime',
            'requested_starts_at' => 'immutable_datetime',
            'series_occurrence_date' => 'immutable_date',
            'status' => AppointmentRequestStatusEnum::class,
            'travel_distance_miles' => 'float',
            'travel_duration_minutes' => 'integer',
        ];
    }
}
