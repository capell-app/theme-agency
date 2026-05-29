<?php

declare(strict_types=1);

namespace Capell\Events\Http\Controllers;

use Capell\Events\Actions\BuildCalendarFeedAction;
use Capell\Frontend\Facades\Frontend;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class CalendarFeedController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $site = Frontend::site();
        $feed = BuildCalendarFeedAction::run($site);
        $etag = hash('sha256', $feed);

        $response = response($feed, 200, [
            'Cache-Control' => 'public, max-age=3600, stale-while-revalidate=3600',
            'Content-Disposition' => 'inline; filename="events.ics"',
            'Content-Type' => 'text/calendar; charset=UTF-8',
        ]);

        $response->setEtag($etag);
        $response->isNotModified($request);

        return $response;
    }
}
