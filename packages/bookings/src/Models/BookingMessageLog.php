<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingMessageStatusEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int|null $appointment_request_id
 * @property int|null $portal_account_id
 * @property int|null $site_id
 * @property BookingMessageChannelEnum $channel
 * @property BookingMessageStatusEnum $status
 * @property string $type
 * @property string $recipient
 */
class BookingMessageLog extends Model
{
    protected $table = 'booking_message_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_request_id',
        'body',
        'channel',
        'error',
        'meta',
        'portal_account_id',
        'provider_message_id',
        'recipient',
        'scheduled_for',
        'sent_at',
        'site_id',
        'status',
        'subject',
        'type',
    ];

    /**
     * @return BelongsTo<AppointmentRequest, $this>
     */
    public function appointmentRequest(): BelongsTo
    {
        return $this->belongsTo(AppointmentRequest::class, 'appointment_request_id');
    }

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
            'meta' => 'json',
            'scheduled_for' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'status' => BookingMessageStatusEnum::class,
        ];
    }
}
