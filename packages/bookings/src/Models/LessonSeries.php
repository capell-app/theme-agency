<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Database\Factories\LessonSeriesFactory;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Override;

/**
 * @property bool $active
 * @property CarbonImmutable $active_from
 * @property CarbonImmutable|null $active_until
 * @property bool $auto_confirm_instances
 * @property int $id
 * @property int $cadence_weeks
 * @property string $customer_email
 * @property string $customer_name
 * @property int|null $portal_account_id
 * @property string|null $customer_phone
 * @property int $day_of_week
 * @property int|null $duration_minutes
 * @property CarbonImmutable|null $materialized_until
 * @property array<string, mixed>|null $meta
 * @property array<string, mixed>|null $payload
 * @property array<string, mixed>|null $reminder_preferences
 * @property int $service_id
 * @property int|null $site_id
 * @property int|null $staff_member_id
 * @property int|null $location_id
 * @property string $starts_at
 * @property string $timezone
 * @property-read BookingLocation|null $location
 * @property-read PortalAccount|null $portalAccount
 * @property-read BookingService $service
 * @property-read Site|null $site
 * @property-read BookingStaffMember|null $staffMember
 */
class LessonSeries extends Model
{
    /** @use HasFactory<LessonSeriesFactory> */
    use HasFactory;

    use SoftDeletes;

    protected $table = 'lesson_series';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'active',
        'active_from',
        'active_until',
        'auto_confirm_instances',
        'cadence_weeks',
        'customer_email',
        'customer_name',
        'customer_phone',
        'day_of_week',
        'duration_minutes',
        'location_id',
        'materialized_until',
        'meta',
        'payload',
        'portal_account_id',
        'reminder_preferences',
        'service_id',
        'site_id',
        'staff_member_id',
        'starts_at',
        'timezone',
    ];

    protected static string $factory = LessonSeriesFactory::class;

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
        return $this->hasMany(AppointmentRequest::class, 'lesson_series_id');
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    protected function scopeActive(Builder $query): Builder
    {
        return $query->where('active', true);
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'active_from' => 'immutable_date',
            'active_until' => 'immutable_date',
            'auto_confirm_instances' => 'boolean',
            'cadence_weeks' => 'integer',
            'day_of_week' => 'integer',
            'duration_minutes' => 'integer',
            'materialized_until' => 'immutable_date',
            'meta' => 'json',
            'payload' => 'json',
            'reminder_preferences' => 'json',
        ];
    }
}
