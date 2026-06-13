<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\ResolveReviewParticipantTokenAction;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowReviewParticipantController
{
    public function __invoke(Request $request, string $token): View
    {
        abort_unless($request->hasValidSignature(), 403);
        $signatureExpiresAt = filter_var($request->query('expires'), FILTER_VALIDATE_INT);
        abort_unless($signatureExpiresAt !== false, 403);

        $reviewParticipant = ResolveReviewParticipantTokenAction::run($token);

        return view('capell-bookings::portal.review-participant', [
            'reviewParticipant' => $reviewParticipant,
            'signatureExpiresAt' => CarbonImmutable::createFromTimestampUTC($signatureExpiresAt),
            'token' => $token,
        ]);
    }
}
