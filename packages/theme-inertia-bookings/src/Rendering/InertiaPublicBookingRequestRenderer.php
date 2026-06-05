<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\InertiaBookings\Rendering;

use Capell\Bookings\Actions\BuildPublicBookingRequestPropsAction;
use Capell\Bookings\Contracts\PublicBookingRequestRenderer;
use Capell\Inertia\Facades\CapellInertia;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InertiaPublicBookingRequestRenderer implements PublicBookingRequestRenderer
{
    public function render(Request $request): Response
    {
        return CapellInertia::render('Capell/Bookings/Request', BuildPublicBookingRequestPropsAction::run($request, lazySlots: true));
    }
}
