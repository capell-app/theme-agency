<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\ResolveReviewRequestTokenAction;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowReviewRequestController
{
    public function __invoke(Request $request, string $token): View
    {
        abort_unless($request->hasValidSignature(), 403);
        $signatureExpiresAt = filter_var($request->query('expires'), FILTER_VALIDATE_INT);
        abort_unless($signatureExpiresAt !== false, 403);

        $reviewRequest = ResolveReviewRequestTokenAction::run($token);

        return view('capell-bookings::portal.review', [
            'reviewRequest' => $reviewRequest,
            'signatureExpiresAt' => CarbonImmutable::createFromTimestampUTC($signatureExpiresAt),
            'token' => $token,
        ]);
    }
}
