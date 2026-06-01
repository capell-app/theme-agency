<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class NormalizeManagedUrlAction
{
    use AsAction;

    public function handle(string $url, bool $allowAbsolute = false): string
    {
        $url = trim($url);

        throw_if($url === '', InvalidArgumentException::class, 'URL cannot be empty.');

        if ($allowAbsolute && $this->isAllowedAbsoluteUrl($url)) {
            return rtrim($url, '/') ?: $url;
        }

        $path = $this->pathFromUrl($url);

        throw_if($path === null || ! str_starts_with($path, '/'), InvalidArgumentException::class, 'Managed URL paths must start with a slash.');

        return $path === '/' ? '/' : rtrim($path, '/');
    }

    private function isAllowedAbsoluteUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        return is_string($scheme)
            && is_string($host)
            && in_array(strtolower($scheme), ['http', 'https'], true);
    }

    private function pathFromUrl(string $url): ?string
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return null;
        }

        $query = parse_url($url, PHP_URL_QUERY);

        if (is_string($query) && $query !== '') {
            return $path . '?' . $query;
        }

        return $path;
    }
}
