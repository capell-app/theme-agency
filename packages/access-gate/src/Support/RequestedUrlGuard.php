<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support;

use Capell\AccessGate\Models\Area;
use Illuminate\Http\Request;

final class RequestedUrlGuard
{
    public function allowed(Request $request, Area $area, mixed $requestedUrl): ?string
    {
        if (! is_string($requestedUrl) || $requestedUrl === '') {
            return null;
        }

        $host = parse_url($requestedUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return null;
        }

        if (! $this->hasHttpScheme($requestedUrl)) {
            return null;
        }

        if ($host === $request->getHost()) {
            return $requestedUrl;
        }

        return in_array($host, $this->allowedHosts($area), true) ? $requestedUrl : null;
    }

    public function redirectUrl(Request $request, Area $area, mixed $requestedUrl): string
    {
        return $this->allowed($request, $area, $requestedUrl) ?? url('/');
    }

    /**
     * Resolve where a claimed user should land: a configured per-area landing
     * URL when present and safe, otherwise the originally requested URL, then
     * the site root. Never returns an untrusted destination.
     */
    public function claimLandingUrl(Request $request, Area $area, mixed $requestedUrl): string
    {
        $landing = $area->claim_landing_url;

        if (is_string($landing) && $landing !== '') {
            if (str_starts_with($landing, '/') && ! str_starts_with($landing, '//')) {
                return url($landing);
            }

            $allowed = $this->allowed($request, $area, $landing);

            if ($allowed !== null) {
                return $allowed;
            }
        }

        return $this->redirectUrl($request, $area, $requestedUrl);
    }

    private function hasHttpScheme(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true);
    }

    /**
     * @return list<string>
     */
    private function allowedHosts(Area $area): array
    {
        return array_values(collect($area->claim_url_hosts ?? [])
            ->filter(fn (mixed $host): bool => is_string($host) && $host !== '')
            ->unique()
            ->values()
            ->all());
    }
}
