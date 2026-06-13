<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingChangeProposalParty;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingChangeProposalParty run(BookingChangeProposalParty $proposalParty, string $token)
 */
class ResolveBookingChangeProposalTokenAction
{
    use AsAction;

    public function handle(BookingChangeProposalParty $proposalParty, string $token): BookingChangeProposalParty
    {
        if (
            $proposalParty->token_hash === null
            || ! hash_equals($proposalParty->token_hash, hash('sha256', $token))
            || ($proposalParty->token_expires_at !== null && $proposalParty->token_expires_at->lessThanOrEqualTo(CarbonImmutable::now()))
        ) {
            throw ValidationException::withMessages([
                'token' => __('capell-bookings::validation.change_proposal_token_invalid'),
            ]);
        }

        return $proposalParty;
    }
}
