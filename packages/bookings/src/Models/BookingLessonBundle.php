<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingLessonBundleStatusEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $site_id
 * @property int|null $portal_account_id
 * @property int|null $service_id
 * @property BookingLessonBundleStatusEnum $status
 * @property string $name
 * @property int $credits_purchased
 * @property int $credits_remaining
 * @property int|null $price_paid_pence
 * @property CarbonImmutable|null $expires_at
 * @property array<string, mixed>|null $meta
 */
class BookingLessonBundle extends Model
{
    protected $table = 'booking_lesson_bundles';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'credits_purchased',
        'credits_remaining',
        'expires_at',
        'meta',
        'name',
        'portal_account_id',
        'price_paid_pence',
        'service_id',
        'site_id',
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

    #[Override]
    protected function casts(): array
    {
        return [
            'credits_purchased' => 'integer',
            'credits_remaining' => 'integer',
            'expires_at' => 'immutable_datetime',
            'meta' => 'json',
            'price_paid_pence' => 'integer',
            'status' => BookingLessonBundleStatusEnum::class,
        ];
    }
}
