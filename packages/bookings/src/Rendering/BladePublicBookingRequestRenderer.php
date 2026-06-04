<?php

declare(strict_types=1);

namespace Capell\Bookings\Rendering;

use Capell\Bookings\Actions\BuildPublicBookingRequestPropsAction;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class BladePublicBookingRequestRenderer implements PublicBookingRequestRenderer
{
    public function render(Request $request): Response
    {
        return response()->view('capell-bookings::request', BuildPublicBookingRequestPropsAction::run(
            request: $request,
            lazySlots: false,
        ));
    }
}
