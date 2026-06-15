<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingChangeProposalParty;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingChangeProposalParty run(BookingChangeProposalParty|string $proposalParty, ?string $token = null)
 */
class ResolveBookingChangeProposalTokenAction
{
    use AsAction;

    public function handle(BookingChangeProposalParty|string $proposalParty, ?string $token = null): BookingChangeProposalParty
    {
        if (is_string($proposalParty)) {
            $token = $proposalParty;

            /** @var BookingChangeProposalParty|null $matchedProposalParty */
            $matchedProposalParty = BookingChangeProposalParty::query()
                ->where('token_hash', hash('sha256', $token))
                ->first();

            if (! $matchedProposalParty instanceof BookingChangeProposalParty) {
                throw $this->invalidToken();
            }

            $proposalParty = $matchedProposalParty;
        }

        if (
            $token === null
            || $token === ''
            || $proposalParty->token_hash === null
            || ! hash_equals($proposalParty->token_hash, hash('sha256', $token))
            || ($proposalParty->token_expires_at !== null && $proposalParty->token_expires_at->lessThanOrEqualTo(CarbonImmutable::now()))
        ) {
            throw $this->invalidToken();
        }

        return $proposalParty;
    }

    private function invalidToken(): ValidationException
    {
        return ValidationException::withMessages([
            'token' => __('capell-bookings::validation.change_proposal_token_invalid'),
        ]);
    }
}
