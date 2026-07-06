<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\Support;

use Capell\Core\Facades\CapellCore;

/**
 * Provider-resolved availability check for the optional `capell-app/blog`
 * package, mirroring `Capell\FoundationTheme\Support\NavigationAvailability`'s
 * shape exactly.
 *
 * Consumed only from real PHP (class-based Blade components, Actions), never
 * from a public `@php` Blade block.
 *
 * When blog is not installed, Call Out simply omits the latest-tips blog
 * rail from its content-listing surfaces rather than rendering an empty
 * or broken rail.
 */
final class BlogAvailability
{
    private const string PACKAGE_NAME = 'capell-app/blog';

    public static function check(): bool
    {
        return CapellCore::isPackageInstalled(self::PACKAGE_NAME);
    }
}
