<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\ResolveBookingChangeProposalTokenAction;
use Capell\Bookings\Models\BookingChangeProposalParty;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowBookingChangeProposalController
{
    public function __invoke(Request $request, BookingChangeProposalParty $proposalParty, string $token): View
    {
        abort_unless($request->hasValidSignature(), 403);

        $proposalParty = ResolveBookingChangeProposalTokenAction::run($proposalParty, $token);

        return view('capell-bookings::portal.proposal', [
            'proposalParty' => $proposalParty->load('proposal.appointmentRequest'),
            'token' => $token,
        ]);
    }
}
