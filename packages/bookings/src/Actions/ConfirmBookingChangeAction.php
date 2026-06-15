<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingChangeProposalPartyStatusEnum;
use Capell\Bookings\Enums\BookingChangeProposalStatusEnum;
use Capell\Bookings\Models\BookingChangeProposal;
use Capell\Bookings\Models\BookingChangeProposalParty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingChangeProposal run(BookingChangeProposal $proposal, string $party, bool $accepted)
 */
class ConfirmBookingChangeAction
{
    use AsAction;

    public function handle(BookingChangeProposal $proposal, string $party, bool $accepted): BookingChangeProposal
    {
        return DB::transaction(function () use ($proposal, $party, $accepted): BookingChangeProposal {
            /** @var BookingChangeProposal $lockedProposal */
            $lockedProposal = BookingChangeProposal::query()
                ->whereKey($proposal->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedProposal->status->isOpen() || ($lockedProposal->expires_at !== null && $lockedProposal->expires_at->lessThanOrEqualTo(CarbonImmutable::now()))) {
                throw ValidationException::withMessages([
                    'status' => __('capell-bookings::validation.change_proposal_closed'),
                ]);
            }

            /** @var BookingChangeProposalParty $proposalParty */
            $proposalParty = $lockedProposal->parties()
                ->where('party', $party)
                ->lockForUpdate()
                ->firstOrFail();

            $proposalParty->forceFill([
                'status' => $accepted ? BookingChangeProposalPartyStatusEnum::Accepted : BookingChangeProposalPartyStatusEnum::Rejected,
                'accepted_at' => $accepted ? CarbonImmutable::now() : null,
                'rejected_at' => $accepted ? null : CarbonImmutable::now(),
            ])->save();

            if (! $accepted) {
                $lockedProposal->forceFill(['status' => BookingChangeProposalStatusEnum::Rejected])->save();

                return $lockedProposal->refresh()->load('parties');
            }

            if ($lockedProposal->parties()->where('status', '!=', BookingChangeProposalPartyStatusEnum::Accepted->value)->doesntExist()) {
                ApplyBookingChangeAction::run($lockedProposal);
            }

            return $lockedProposal->refresh()->load('parties');
        });
    }
}
