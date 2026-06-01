<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\BuildPublicBookingRequestOptionsAction;
use Illuminate\Http\Response;

final class ShowBookingRequestController
{
    public function __invoke(): Response
    {
        return $this->noStore(response()->view('capell-bookings::request', [
            'options' => BuildPublicBookingRequestOptionsAction::run(),
        ]));
    }

    private function noStore(Response $response): Response
    {
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
