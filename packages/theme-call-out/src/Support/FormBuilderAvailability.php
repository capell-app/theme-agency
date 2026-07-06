<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\Support;

use Capell\Core\Facades\CapellCore;

/**
 * Provider-resolved availability check for the optional `capell-app/form-builder`
 * package, mirroring `Capell\FoundationTheme\Support\NavigationAvailability`'s
 * shape exactly.
 *
 * Consumed only from real PHP (class-based Blade components, Actions), never
 * from a public `@php` Blade block — see `AssertsPublicThemeOutputSafety`'s
 * `@php` policy, which bans static calls other than `data_get`/`collect`/
 * `trans`/`__`/`Str`/`Arr` inside `@php ... @endphp`.
 *
 * When form-builder is not installed, Call Out's quote-request surfaces fall
 * back to a plain `mailto:` contact card instead of a real embedded form.
 */
final class FormBuilderAvailability
{
    private const string PACKAGE_NAME = 'capell-app/form-builder';

    public static function check(): bool
    {
        return CapellCore::isPackageInstalled(self::PACKAGE_NAME);
    }
}
