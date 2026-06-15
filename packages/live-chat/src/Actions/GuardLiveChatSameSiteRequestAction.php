<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Models\LiveChatInstallation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @method static void run(LiveChatInstallation $installation, Request $request)
 */
final class GuardLiveChatSameSiteRequestAction
{
    use AsAction;

    public function handle(LiveChatInstallation $installation, Request $request): void
    {
        if (! $installation->is_active) {
            throw new HttpException(403, 'Live chat installation is not active.');
        }

        $origin = $this->requestOrigin($request);

        if ($origin === null) {
            throw new HttpException(403, 'Live chat same-site origin is required.');
        }

        $originHost = $this->host($origin);
        $requestHost = $this->normalizeHost($request->getHost());

        if ($originHost === null || $requestHost === null || $originHost !== $requestHost) {
            throw new HttpException(403, 'Live chat same-site origin is not allowed.');
        }
    }

    private function requestOrigin(Request $request): ?string
    {
        $origin = $request->headers->get('Origin');

        if (is_string($origin) && trim($origin) !== '') {
            return $this->origin(trim($origin));
        }

        $referer = $request->headers->get('Referer');

        return is_string($referer) && trim($referer) !== '' ? $this->origin(trim($referer)) : null;
    }

    private function host(string $value): ?string
    {
        $candidate = trim($value);

        if ($candidate === '') {
            return null;
        }

        $url = str_contains($candidate, '://') ? $candidate : 'https://' . $candidate;
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && trim($host) !== '' ? $this->normalizeHost($host) : null;
    }

    private function origin(string $value): ?string
    {
        $candidate = trim($value);

        if ($candidate === '') {
            return null;
        }

        $url = str_contains($candidate, '://') ? $candidate : 'https://' . $candidate;
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);
        $port = parse_url($url, PHP_URL_PORT);

        if (! is_string($scheme) || ! is_string($host)) {
            return null;
        }

        return Str::lower($scheme . '://' . $host . (is_int($port) ? ':' . $port : ''));
    }

    private function normalizeHost(string $host): ?string
    {
        $normalized = Str::lower(trim($host));

        if ($normalized === '') {
            return null;
        }

        return preg_replace('/:\d+$/', '', $normalized) ?: null;
    }
}
