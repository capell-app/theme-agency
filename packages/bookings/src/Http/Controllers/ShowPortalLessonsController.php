<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\BuildPortalLessonRowsAction;
use Capell\Bookings\Actions\ResolvePortalAccessTokenAction;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowPortalLessonsController
{
    public function __invoke(Request $request, string $portalToken): View
    {
        abort_unless($request->hasValidSignature(), 403);

        $resolved = ResolvePortalAccessTokenAction::run($portalToken);
        /** @var PortalAccount $portalAccount */
        $portalAccount = $resolved['portal_account'];
        $siteId = $resolved['site_id'];

        return view('capell-bookings::portal.lessons', [
            'portalAccount' => $portalAccount,
            'rows' => BuildPortalLessonRowsAction::run($siteId, $portalAccount),
        ]);
    }
}
