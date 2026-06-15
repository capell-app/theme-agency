<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\CaptureReviewParticipantResponseAction;
use Capell\Bookings\Actions\ResolveReviewParticipantTokenAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StoreReviewParticipantController
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);

        $reviewParticipant = ResolveReviewParticipantTokenAction::run($token);

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'response' => ['nullable', 'string', 'max:2000'],
        ]);

        CaptureReviewParticipantResponseAction::run(
            reviewParticipant: $reviewParticipant,
            token: $token,
            rating: (int) $validated['rating'],
            response: is_string($validated['response'] ?? null) ? $validated['response'] : null,
        );

        return back()->with('booking_portal_status', __('capell-bookings::portal.review_saved'));
    }
}
