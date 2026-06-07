<?php

declare(strict_types=1);

namespace Capell\FrontendAuthoring\Actions;

use Capell\Core\Actions\LoadSiteDomainFromUrlAction;
use Capell\Core\Models\PageUrl;
use Capell\Core\Models\SiteDomain;
use Capell\Frontend\Contracts\AdminAccessCheckerInterface;
use Capell\Frontend\Support\Loader\SiteLoader;
use Capell\FrontendAuthoring\Http\Requests\BeaconRequest;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildBeaconResponseAction
{
    use AsObject;

    public function handle(BeaconRequest $request): JsonResponse
    {
        $data = [
            'csrf_token' => csrf_token(),
        ];

        // Anonymous/non-same-origin beacons must stay O(1) and never resolve page data.
        if ($request->user() === null || ! $this->isSameOriginRequest($request) || ! $this->isPostedUrlSameOrigin($request)) {
            return response()->json($data);
        }

        $resolvedSiteDomain = LoadSiteDomainFromUrlAction::run($request->url, sites: SiteLoader::getSites());

        if (! is_array($resolvedSiteDomain)) {
            return response()->json([
                'message' => 'Not Found',
            ], 404);
        }

        [$siteDomain, $url] = $resolvedSiteDomain;

        if (! $siteDomain instanceof SiteDomain) {
            return response()->json([
                'message' => 'Not Found',
            ], 404);
        }

        $user = $request->user();

        if ($this->isAdminUser($user) && config('capell-frontend-authoring.enabled') === true) {
            /** @var User $user */
            $data['user'] = [
                'id' => $user->getKey(),
                'name' => (string) data_get($user, 'name'),
                'admin' => true,
            ];
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

    private function isSameOriginRequest(BeaconRequest $request): bool
    {
        $secFetchSite = $request->headers->get('Sec-Fetch-Site');
        if (is_string($secFetchSite) && $secFetchSite !== '') {
            return $secFetchSite === 'same-origin' || $secFetchSite === 'none';
        }

        $origin = $request->headers->get('Origin');
        if (! is_string($origin) || $origin === '') {
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
            && $this->normalisePort($scheme, $port) === $this->normalisePort(
                $request->getScheme(),
                is_numeric($request->getPort()) ? (int) $request->getPort() : null,
            );
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
