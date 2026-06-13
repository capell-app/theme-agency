<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\BookingChangeProposalPartyStatusEnum;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property int $id
 * @property int $booking_change_proposal_id
 * @property int|null $portal_account_id
 * @property string $party
 * @property CarbonImmutable|null $accepted_at
 * @property CarbonImmutable|null $rejected_at
 * @property CarbonImmutable|null $token_expires_at
 * @property BookingChangeProposalPartyStatusEnum $status
 */
class BookingChangeProposalParty extends Model
{
    protected $table = 'booking_change_proposal_parties';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'accepted_at',
        'booking_change_proposal_id',
        'meta',
        'party',
        'portal_account_id',
        'rejected_at',
        'status',
        'token_expires_at',
        'token_hash',
    ];

    /**
     * @return BelongsTo<BookingChangeProposal, $this>
     */
    public function proposal(): BelongsTo
    {
        return $this->belongsTo(BookingChangeProposal::class, 'booking_change_proposal_id');
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
            'accepted_at' => 'immutable_datetime',
            'meta' => 'json',
            'rejected_at' => 'immutable_datetime',
            'status' => BookingChangeProposalPartyStatusEnum::class,
            'token_expires_at' => 'immutable_datetime',
        ];
    }
}
