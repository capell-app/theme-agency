<?php

declare(strict_types=1);

namespace Capell\AccessGate\Support;

final class AnnouncementLinkUrl
{
    public static function isAllowed(string $url): bool
    {
        $trimmedUrl = trim($url);

        if ($trimmedUrl === '' || preg_match('/[\x00-\x1F\x7F]/', $trimmedUrl) === 1) {
            return false;
        }

        if (str_starts_with($trimmedUrl, '/') && ! str_starts_with($trimmedUrl, '//')) {
            return true;
        }

        $scheme = parse_url($trimmedUrl, PHP_URL_SCHEME);
        $host = parse_url($trimmedUrl, PHP_URL_HOST);

        return is_string($scheme)
            && in_array(strtolower($scheme), ['http', 'https'], true)
            && is_string($host)
            && trim($host) !== '';
    }
}
