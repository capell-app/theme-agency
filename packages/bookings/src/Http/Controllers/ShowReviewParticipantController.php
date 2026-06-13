<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\ResolveReviewParticipantTokenAction;
use Capell\Bookings\Models\BookingReviewParticipant;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowReviewParticipantController
{
    public function __invoke(Request $request, BookingReviewParticipant $reviewParticipant, string $token): View
    {
        abort_unless($request->hasValidSignature(), 403);

        ResolveReviewParticipantTokenAction::run($reviewParticipant, $token);

        return view('capell-bookings::portal.review-participant', [
            'reviewParticipant' => $reviewParticipant,
            'token' => $token,
        ]);
    }
}
