<?php

declare(strict_types=1);

namespace Capell\UrlManager\Actions;

use InvalidArgumentException;
use Lorisleiva\Actions\Concerns\AsAction;

final class BuildCanonicalUrlAction
{
    use AsAction;

    public function handle(string $url): string
    {
        $url = trim($url);

        throw_if($url === '', InvalidArgumentException::class, __('capell-url-manager::validation.url_empty'));

        $scheme = $this->configuredScheme($url);
        $host = $this->configuredHost($url);
        $path = $this->canonicalPath((string) parse_url($url, PHP_URL_PATH));
        $query = $this->canonicalQuery((string) parse_url($url, PHP_URL_QUERY));

        if ($host === null) {
            return $path . ($query === null ? '' : '?' . $query);
        }

        return $scheme . '://' . $host . $path . ($query === null ? '' : '?' . $query);
    }

    private function configuredScheme(string $url): string
    {
        $configured = config('capell-url-manager.canonical.scheme');

        if (is_string($configured) && in_array(strtolower($configured), ['http', 'https'], true)) {
            return strtolower($configured);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return is_string($scheme) && in_array(strtolower($scheme), ['http', 'https'], true)
            ? strtolower($scheme)
            : 'https';
    }

    private function configuredHost(string $url): ?string
    {
        $configured = config('capell-url-manager.canonical.host');

        if (is_string($configured) && trim($configured) !== '') {
            return strtolower(trim($configured));
        }

        $host = parse_url($url, PHP_URL_HOST);

        return is_string($host) && $host !== '' ? strtolower($host) : null;
    }

    private function canonicalPath(string $path): string
    {
        $path = $path === '' ? '/' : $path;

        if ((bool) config('capell-url-manager.canonical.lowercase_path', true)) {
            $path = strtolower($path);
        }

        return match (config('capell-url-manager.canonical.trailing_slash', 'remove')) {
            'add' => $path === '/' ? '/' : rtrim($path, '/') . '/',
            default => $path === '/' ? '/' : rtrim($path, '/'),
        };
    }

    private function canonicalQuery(string $query): ?string
    {
        if ($query === '') {
            return null;
        }

        parse_str($query, $parameters);

        $stripKeys = $this->configuredStripQueryKeys();

        $filtered = [];

        foreach ($parameters as $key => $value) {
            if (in_array(strtolower((string) $key), $stripKeys, true)) {
                continue;
            }

            $filtered[(string) $key] = $value;
        }

        if ($filtered === []) {
            return null;
        }

        return http_build_query($filtered);
    }

    /**
     * @return list<string>
     */
    private function configuredStripQueryKeys(): array
    {
        $configuredKeys = config('capell-url-manager.canonical.strip_query_keys', []);

        if (! is_array($configuredKeys)) {
            return [];
        }

        $keys = [];

        foreach ($configuredKeys as $key) {
            if (is_string($key) && $key !== '') {
                $keys[] = strtolower($key);
            }
        }

        return array_values(array_unique($keys));
    }
}
