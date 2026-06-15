<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingWaitlistStatusEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $service_id
 * @property int|null $staff_member_id
 * @property int|null $location_id
 * @property int|null $portal_account_id
 * @property BookingWaitlistStatusEnum $status
 * @property string $customer_name
 * @property string $customer_email
 * @property string|null $customer_phone
 * @property CarbonImmutable|null $preferred_starts_at
 * @property CarbonImmutable|null $preferred_ends_at
 * @property CarbonImmutable|null $offered_at
 * @property CarbonImmutable|null $offer_expires_at
 * @property array<string, mixed>|null $preferences
 * @property array<string, mixed>|null $meta
 */
class BookingWaitlistEntry extends Model
{
    protected $table = 'booking_waitlist_entries';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'customer_email',
        'customer_name',
        'customer_phone',
        'location_id',
        'meta',
        'offer_expires_at',
        'offered_at',
        'portal_account_id',
        'preferred_ends_at',
        'preferred_starts_at',
        'preferences',
        'service_id',
        'site_id',
        'staff_member_id',
        'status',
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
     * @return BelongsTo<PortalAccount, $this>
     */
    public function portalAccount(): BelongsTo
    {
        return $this->belongsTo(PortalAccount::class, 'portal_account_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'meta' => 'json',
            'offer_expires_at' => 'immutable_datetime',
            'offered_at' => 'immutable_datetime',
            'preferred_ends_at' => 'immutable_datetime',
            'preferred_starts_at' => 'immutable_datetime',
            'preferences' => 'json',
            'status' => BookingWaitlistStatusEnum::class,
        ];
    }
}
