<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\BuildPortalLessonRowsAction;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowPortalLessonsController
{
    public function __invoke(Request $request, int $site, PortalAccount $portalAccount): View
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($portalAccount->site_id === $site, 404);

        return view('capell-bookings::portal.lessons', [
            'portalAccount' => $portalAccount,
            'rows' => BuildPortalLessonRowsAction::run($site, $portalAccount),
        ]);
    }
}
