<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Models\BookingReviewRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowReviewRequestController
{
    public function __invoke(Request $request, BookingReviewRequest $reviewRequest): View
    {
        abort_unless($request->hasValidSignature(), 403);

        return view('capell-bookings::portal.review', [
            'reviewRequest' => $reviewRequest,
        ]);
    }
}
