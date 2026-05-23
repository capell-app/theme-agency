<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Http\Controllers;

use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Models\PageUrl;
use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
use Capell\Frontend\Support\Loader\PageLoader;
use Capell\Frontend\Support\Loader\SiteLoader;
use Capell\FrontendAuthoring\Actions\BuildAuthoringBannerContextAction;
use Capell\FrontendAuthoring\Actions\BuildEditableRegionManifestAction;
use Capell\FrontendAuthoring\Http\Requests\BeaconRequest;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller as BaseController;

class BeaconController extends BaseController
{
    public function __invoke(BeaconRequest $request): JsonResponse
    {
        $data = [
            'csrf_token' => csrf_token(),
        ];

        // Anonymous beacon must be O(1) — never resolve site/page for unauthenticated requests.
        if ($request->user() === null) {
            return response()->json($data);
        }

        // Cross-origin beacons must never receive the admin manifest, even when the
        // browser presents a valid session cookie (defence-in-depth against CSRF /
        // cross-site embedding leaking editor metadata to other origins).
        if (! $this->isSameOriginRequest($request)) {
            return response()->json($data);
        }

        [$siteDomain, $url] = LoadSiteDomainFromUrlAction::run($request->url, sites: SiteLoader::getSites());

        if (! $siteDomain) {
            return response()->json([
                'message' => 'Not Found',
            ], 404);
        }

        $pageUrl = null;

        PageUrl::withoutEvents(function () use ($siteDomain, $url, &$pageUrl): void {
            $pageUrl = PageLoader::getPageUrl(
                site: $siteDomain->site,
                language: $siteDomain->language,
                url: $url,
                withEvents: false,
            );

            if (! $pageUrl instanceof PageUrl) {
                $pageUrl = PageLoader::getWildCardUrl(
                    site: $siteDomain->site,
                    language: $siteDomain->language,
                    url: $url,
                    withEvents: false,
                );
            }
        });

        /** @var User $user */
        $user = $request->user();

        $data['user'] = [
            'id' => $user->getKey(),
            'name' => (string) data_get($user, 'name'),
        ];

        if ($this->isAdminUser($user) && config('capell-frontend-authoring.enabled') === true) {
            $data['user']['admin'] = true;

            if ($pageUrl instanceof PageUrl) {
                $data['scripts'] = [
                    view('capell::authoring.bootstrap-script', [
                        'banner' => BuildAuthoringBannerContextAction::run($pageUrl),
                        'regions' => BuildEditableRegionManifestAction::run($pageUrl),
                    ])->render(),
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * Confirm the beacon request originated from the same origin as the host we serve.
     * Browsers send Sec-Fetch-Site for fetch/XHR; we treat anything other than
     * "same-origin" as cross-origin. When that header is absent (older clients,
     * server-to-server), fall back to comparing the Origin header host with the
     * request host.
     */
    private function isSameOriginRequest(BeaconRequest $request): bool
    {
        $secFetchSite = $request->headers->get('Sec-Fetch-Site');
        if (is_string($secFetchSite) && $secFetchSite !== '') {
            return $secFetchSite === 'same-origin' || $secFetchSite === 'none';
        }

        $origin = $request->headers->get('Origin');
        if (! is_string($origin) || $origin === '') {
            // No Origin header — likely same-origin navigation/XHR pre-Sec-Fetch.
            return true;
        }

        $originHost = parse_url($origin, PHP_URL_HOST);
        if (! is_string($originHost) || $originHost === '') {
            return false;
        }

        return strcasecmp($originHost, $request->getHost()) === 0;
    }

    private function isAdminUser(AuthenticatableContract $user): bool
    {
        $checker = resolve(AdminAccessCheckerInterface::class);

        return $checker->isAdmin($user);
    }
}
