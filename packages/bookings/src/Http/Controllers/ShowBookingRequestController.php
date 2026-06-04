<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

final class ShowBookingRequestController
{
    public function __invoke(Request $request, PublicBookingRequestRenderer $renderer): SymfonyResponse
    {
        return $this->noStore($renderer->render($request));
    }

    private function noStore(SymfonyResponse $response): SymfonyResponse
    {
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }
}
