<?php

declare(strict_types=1);

namespace Capell\Bookings\Contracts;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface PublicBookingRequestRenderer
{
    public function render(Request $request): Response;
}
