<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $booking_review_request_id
 * @property int|null $portal_account_id
 * @property string $role
 * @property string|null $name
 * @property string|null $email
 * @property BookingReviewParticipantStatusEnum $status
 * @property bool $required
 * @property string|null $token_hash
 * @property CarbonImmutable|null $token_expires_at
 * @property CarbonImmutable|null $sent_at
 * @property int|null $rating
 * @property string|null $response
 * @property CarbonImmutable|null $completed_at
 * @property array<string, mixed>|null $meta
 * @property-read BookingReviewRequest|null $reviewRequest
 */
class BookingReviewParticipant extends Model
{
    protected $table = 'booking_review_participants';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'booking_review_request_id',
        'completed_at',
        'email',
        'meta',
        'name',
        'portal_account_id',
        'rating',
        'required',
        'response',
        'role',
        'sent_at',
        'status',
        'token_expires_at',
        'token_hash',
    ];

    /**
     * @return BelongsTo<BookingReviewRequest, $this>
     */
    public function reviewRequest(): BelongsTo
    {
        return $this->belongsTo(BookingReviewRequest::class, 'booking_review_request_id');
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
            'completed_at' => 'immutable_datetime',
            'meta' => 'json',
            'rating' => 'integer',
            'required' => 'boolean',
            'sent_at' => 'immutable_datetime',
            'status' => BookingReviewParticipantStatusEnum::class,
            'token_expires_at' => 'immutable_datetime',
        ];
    }
}
