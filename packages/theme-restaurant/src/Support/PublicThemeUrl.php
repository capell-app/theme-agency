<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\Restaurant\Support;

final class PublicThemeUrl
{
    public static function link(mixed $url): ?string
    {
        return self::sanitize($url, allowFragment: true);
    }

    public static function formAction(mixed $url): ?string
    {
        return self::sanitize($url, allowFragment: false);
    }

    private static function sanitize(mixed $url, bool $allowFragment): ?string
    {
        if (! is_string($url)) {
            return null;
        }

        $candidateUrl = trim($url);

        if ($candidateUrl === '' || preg_match('/[\x00-\x1F\x7F]/', $candidateUrl) === 1) {
            return null;
        }

        if (str_starts_with($candidateUrl, '#')) {
            return $allowFragment && preg_match('/^#[A-Za-z][A-Za-z0-9_-]*$/', $candidateUrl) === 1
                ? $candidateUrl
                : null;
        }

        if (str_starts_with($candidateUrl, '//')) {
            return null;
        }

        $parsedUrl = parse_url($candidateUrl);

        if (! is_array($parsedUrl)) {
            return null;
        }

        $scheme = $parsedUrl['scheme'] ?? null;

        if (is_string($scheme) && ! in_array(strtolower($scheme), ['http', 'https'], true)) {
            return null;
        }

        if ($scheme === null && ! str_starts_with($candidateUrl, '/')) {
            return null;
        }

        if (self::containsRestrictedSurface($parsedUrl)) {
            return null;
        }

        return $candidateUrl;
    }

    /**
     * @param  array<string, int|string>  $parsedUrl
     */
    private static function containsRestrictedSurface(array $parsedUrl): bool
    {
        $path = strtolower(rawurldecode((string) ($parsedUrl['path'] ?? '')));
        $pathSegments = array_values(array_filter(
            explode('/', $path),
            static fn (string $pathSegment): bool => $pathSegment !== '',
        ));

        if (array_intersect($pathSegments, ['admin', 'filament', 'livewire']) !== []) {
            return true;
        }

        $query = strtolower(rawurldecode((string) ($parsedUrl['query'] ?? '')));

        return preg_match('/(^|[&;])(signature|signed|_token)=/', $query) === 1;
    }
}
