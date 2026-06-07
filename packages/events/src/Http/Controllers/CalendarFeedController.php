<?php

declare(strict_types=1);

namespace Capell\Events\Http\Controllers;

use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\Events\Actions\BuildCalendarFeedAction;
use Capell\Frontend\Facades\Frontend;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;

class CalendarFeedController extends BaseController
{
    public function __invoke(Request $request): Response
    {
        $site = Frontend::site();

        abort_unless($site instanceof Site, 404);

        $feed = BuildCalendarFeedAction::run(
            site: $site,
            listingPage: $this->listingPage($request, $site),
        );
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

    private function listingPage(Request $request, Site $site): ?Page
    {
        $listingPage = $request->route('listingPage');

        if ($listingPage instanceof Page) {
            return $listingPage;
        }

        if (! is_string($listingPage) || $listingPage === '') {
            return null;
        }

        return Page::query()
            ->where('site_id', $site->getKey())
            ->where(function (Builder $query) use ($listingPage): void {
                if (is_numeric($listingPage)) {
                    $query->whereKey((int) $listingPage);
                }

                $query->orWhereHas('translations', function (Builder $query) use ($listingPage): void {
                    $query->where('meta->slug', $listingPage);
                });
            })
            ->first();
    }
}
