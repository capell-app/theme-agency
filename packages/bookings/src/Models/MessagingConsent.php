<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\MessagingConsentStatusEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property BookingMessageChannelEnum $channel
 * @property int $portal_account_id
 * @property int $site_id
 * @property MessagingConsentStatusEnum $status
 */
class MessagingConsent extends Model
{
    protected $table = 'booking_messaging_consents';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'channel',
        'consented_at',
        'evidence',
        'portal_account_id',
        'recipient',
        'revoked_at',
        'site_id',
        'status',
    ];

    /**
     * @return BelongsTo<PortalAccount, $this>
     */
    public function portalAccount(): BelongsTo
    {
        return $this->belongsTo(PortalAccount::class, 'portal_account_id');
    }

    /**
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class, 'site_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'channel' => BookingMessageChannelEnum::class,
            'consented_at' => 'immutable_datetime',
            'evidence' => 'json',
            'revoked_at' => 'immutable_datetime',
            'status' => MessagingConsentStatusEnum::class,
        ];
    }
}
