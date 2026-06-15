<?php

declare(strict_types=1);

namespace Capell\LiveChat\Actions;

use Capell\LiveChat\Models\LiveChatInstallation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @method static string run(LiveChatInstallation $installation, Request $request)
 */
final class GuardLiveChatInstallationOriginAction
{
    use AsAction;

    public function handle(LiveChatInstallation $installation, Request $request): string
    {
        $origin = $this->requestOrigin($request);

        if ($origin === null) {
            throw new HttpException(403, 'Live chat origin is not allowed.');
        }

        $originHost = $this->host($origin);

        if ($originHost === null || ! $this->allowsHost($installation, $originHost)) {
            throw new HttpException(403, 'Live chat origin is not allowed.');
        }

        return $origin;
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

    private function allowsHost(LiveChatInstallation $installation, string $originHost): bool
    {
        $domains = $installation->allowed_domains ?? [];

        foreach ($domains as $domain) {
            if (! is_string($domain) || trim($domain) === '') {
                continue;
            }

            $allowedHost = $this->host($domain) ?? $this->normalizeHost($domain);

            if ($allowedHost === '*') {
                return ! app()->isProduction();
            }

            if ($allowedHost !== null && Str::is($allowedHost, $originHost)) {
                return true;
            }
        }

        return false;
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
