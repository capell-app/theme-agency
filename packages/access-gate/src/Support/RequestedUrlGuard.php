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
