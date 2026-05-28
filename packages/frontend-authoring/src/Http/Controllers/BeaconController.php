<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Http\Controllers;

use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\SiteDomain;
use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
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

        if (! $this->isPostedUrlSameOrigin($request)) {
            return response()->json($data);
        }

        [$siteDomain, $url] = LoadSiteDomainFromUrlAction::run($request->url, sites: SiteLoader::getSites());

        if (! $siteDomain) {
            return response()->json([
                'message' => 'Not Found',
            ], 404);
        }

        /** @var User $user */
        $user = $request->user();

        $data['user'] = [
            'id' => $user->getKey(),
            'name' => (string) data_get($user, 'name'),
        ];

        if ($this->isAdminUser($user) && config('capell-frontend-authoring.enabled') === true) {
            $data['user']['admin'] = true;
            $pageUrl = $this->resolveEditablePageUrl($siteDomain, $url);

            if ($pageUrl instanceof PageUrl) {
                $data['scripts'] = [
                    view('capell::authoring.bootstrap-script', [
                        'banner' => BuildAuthoringBannerContextAction::run($pageUrl),
                        'regions' => BuildEditableRegionManifestAction::run($pageUrl, $user),
                    ])->render(),
                ];
            }
        }

        return response()->json($data);
    }

    private function resolveEditablePageUrl(SiteDomain $siteDomain, string $url): ?PageUrl
    {
        return PageUrl::withoutEvents(
            fn (): ?PageUrl => PageUrl::query()
                ->with(['pageable.translation', 'translation', 'siteDomain'])
                ->where('site_id', $siteDomain->site_id)
                ->where('language_id', $siteDomain->language_id)
                ->where('url', $url)
                ->enabled()
                ->first(),
        );
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

        $originScheme = parse_url($origin, PHP_URL_SCHEME);
        $originPort = parse_url($origin, PHP_URL_PORT);

        return is_string($originScheme)
            && $this->originsMatch($originScheme, $originHost, is_int($originPort) ? $originPort : null, $request);
    }

    private function isPostedUrlSameOrigin(BeaconRequest $request): bool
    {
        $scheme = parse_url($request->url, PHP_URL_SCHEME);
        $host = parse_url($request->url, PHP_URL_HOST);
        $port = parse_url($request->url, PHP_URL_PORT);

        if (! is_string($scheme) || ! is_string($host) || ! in_array($scheme, ['http', 'https'], true)) {
            return false;
        }

        return $this->originsMatch($scheme, $host, is_int($port) ? $port : null, $request);
    }

    private function originsMatch(string $scheme, string $host, ?int $port, BeaconRequest $request): bool
    {
        return $scheme === $request->getScheme()
            && strcasecmp($host, $request->getHost()) === 0
            && $this->normalisePort($scheme, $port) === $this->normalisePort($request->getScheme(), $request->getPort());
    }

    private function normalisePort(string $scheme, ?int $port): int
    {
        if ($port !== null) {
            return $port;
        }

        return $scheme === 'https' ? 443 : 80;
    }

    private function isAdminUser(AuthenticatableContract $user): bool
    {
        $checker = resolve(AdminAccessCheckerInterface::class);

        return $checker->isAdmin($user);
    }
}
