<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Core\Models\Site;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property BookingReviewRequestStatusEnum $status
 * @property int $appointment_request_id
 * @property int|null $portal_account_id
 * @property int|null $site_id
 * @property CarbonImmutable|null $completed_at
 * @property int|null $rating
 * @property string|null $response
 * @property CarbonImmutable $scheduled_for
 * @property CarbonImmutable|null $sent_at
 * @property string|null $token_hash
 * @property CarbonImmutable|null $token_expires_at
 * @property array<string, mixed>|null $meta
 * @property-read AppointmentRequest|null $appointmentRequest
 */
class BookingReviewRequest extends Model
{
    protected $table = 'booking_review_requests';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_request_id',
        'completed_at',
        'meta',
        'portal_account_id',
        'rating',
        'requested_at',
        'response',
        'scheduled_for',
        'sent_at',
        'site_id',
        'status',
        'token_expires_at',
        'token_hash',
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

    /**
     * @return HasMany<BookingReviewParticipant, $this>
     */
    public function participants(): HasMany
    {
        return $this->hasMany(BookingReviewParticipant::class, 'booking_review_request_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'completed_at' => 'immutable_datetime',
            'meta' => 'json',
            'rating' => 'integer',
            'requested_at' => 'immutable_datetime',
            'scheduled_for' => 'immutable_datetime',
            'sent_at' => 'immutable_datetime',
            'status' => BookingReviewRequestStatusEnum::class,
            'token_expires_at' => 'immutable_datetime',
        ];
    }
}
