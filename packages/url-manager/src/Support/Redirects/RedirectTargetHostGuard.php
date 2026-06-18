<?php

declare(strict_types=1);

namespace Capell\UrlManager\Support\Redirects;

/**
 * Shared allowlist check for absolute redirect target hosts.
 *
 * Used both at save time (validating the literal rule template) and at runtime
 * (validating the host of the URL actually produced after regex backreference
 * substitution). The runtime check is the defence-in-depth point: a regex rule
 * may store a template such as "https://$1.example.org/" whose literal host
 * ("$1.example.org") differs from the host produced once the backreference is
 * substituted at request time.
 */
final class RedirectTargetHostGuard
{
    /**
     * Determine whether the given absolute (or relative) target URL is allowed.
     *
     * Relative targets (no host component) are always allowed; only absolute
     * targets whose host falls outside the configured allowlist are rejected.
     */
    public static function isAllowedTargetUrl(string $targetUrl): bool
    {
        $host = parse_url($targetUrl, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return true;
        }

        return in_array(strtolower($host), self::allowedAbsoluteTargetHosts(), true);
    }

    /**
     * @return list<string>
     */
    public static function allowedAbsoluteTargetHosts(): array
    {
        $configuredHosts = [];
        $configuredHostValues = config('capell-url-manager.redirects.absolute_target_allowed_hosts', []);

        if (is_array($configuredHostValues)) {
            foreach ($configuredHostValues as $configuredHost) {
                if (is_string($configuredHost) && trim($configuredHost) !== '') {
                    $configuredHosts[] = strtolower(trim($configuredHost));
                }
            }
        }

        if ((bool) config('capell-url-manager.redirects.allow_app_url_host', true)) {
            $appHost = parse_url((string) config('app.url'), PHP_URL_HOST);

            if (is_string($appHost) && $appHost !== '') {
                $configuredHosts[] = strtolower($appHost);
            }
        }

        return array_values(array_unique($configuredHosts));
    }
}
