<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(string $url, bool $allowAbsolute = false)
 */
final class NormalizeManagedUrlAction
{
    use AsAction;

    public function handle(string $url, bool $allowAbsolute = false): string
    {
        $url = trim($url);

        throw_if($url === '', InvalidArgumentException::class, __('capell-url-manager::validation.url_empty'));

        if ($allowAbsolute && $this->isAllowedAbsoluteUrl($url)) {
            return $this->normalizeAbsoluteUrl($url);
        }

        $path = $this->pathFromUrl($url);

        throw_if($path === null || ! str_starts_with($path, '/'), InvalidArgumentException::class, __('capell-url-manager::validation.path_must_start_with_slash'));

        $normalizedPath = strtolower($path);

        return $normalizedPath === '/' ? '/' : rtrim($normalizedPath, '/');
    }

    private function isAllowedAbsoluteUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($scheme)
            && is_string($host)
            && in_array(strtolower($scheme), ['http', 'https'], true);
    }

    private function normalizeAbsoluteUrl(string $url): string
    {
        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        $port = parse_url($url, PHP_URL_PORT);
        $path = parse_url($url, PHP_URL_PATH);
        $query = parse_url($url, PHP_URL_QUERY);

        $normalizedPath = is_string($path) && $path !== '' ? strtolower($path) : '/';
        $normalizedPath = $normalizedPath === '/' ? '/' : rtrim($normalizedPath, '/');

        $authority = $host . (is_int($port) ? ':' . $port : '');

        return $scheme . '://' . $authority . $normalizedPath . (is_string($query) && $query !== '' ? '?' . $query : '');
    }

    private function pathFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        return $path;
    }
}
