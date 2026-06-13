<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\ConfirmBookingChangeAction;
use Capell\Bookings\Actions\ResolveBookingChangeProposalTokenAction;
use Capell\Bookings\Models\BookingChangeProposal;
use Capell\Bookings\Models\BookingChangeProposalParty;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class RespondBookingChangeProposalController
{
    public function __invoke(Request $request, BookingChangeProposalParty $proposalParty, string $token): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $proposalParty = ResolveBookingChangeProposalTokenAction::run($proposalParty, $token);
        $proposal = $proposalParty->proposal;
        abort_unless($proposal instanceof BookingChangeProposal, 404);

        $accepted = $request->boolean('accepted');
        ConfirmBookingChangeAction::run($proposal, $proposalParty->party, $accepted);

        return back()->with('booking_portal_status', $accepted
            ? __('capell-bookings::portal.proposal_accepted')
            : __('capell-bookings::portal.proposal_rejected'));
    }
}
