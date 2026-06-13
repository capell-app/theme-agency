<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingChangeProposalStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Override;

/**
 * @property int $id
 * @property int $appointment_request_id
 * @property CarbonImmutable|null $expires_at
 * @property CarbonImmutable $proposed_ends_at
 * @property CarbonImmutable $proposed_starts_at
 * @property BookingChangeProposalStatusEnum $status
 * @property-read AppointmentRequest|null $appointmentRequest
 */
class BookingChangeProposal extends Model
{
    protected $table = 'booking_change_proposals';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_request_id',
        'expires_at',
        'meta',
        'proposed_ends_at',
        'proposed_starts_at',
        'reason',
        'status',
    ];

    /**
     * @return BelongsTo<AppointmentRequest, $this>
     */
    public function appointmentRequest(): BelongsTo
    {
        return $this->belongsTo(AppointmentRequest::class, 'appointment_request_id');
    }

    /**
     * @return HasMany<BookingChangeProposalParty, $this>
     */
    public function parties(): HasMany
    {
        return $this->hasMany(BookingChangeProposalParty::class, 'booking_change_proposal_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'expires_at' => 'immutable_datetime',
            'meta' => 'json',
            'proposed_ends_at' => 'immutable_datetime',
            'proposed_starts_at' => 'immutable_datetime',
            'status' => BookingChangeProposalStatusEnum::class,
        ];
    }
}
