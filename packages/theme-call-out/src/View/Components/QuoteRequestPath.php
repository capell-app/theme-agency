<?php

declare(strict_types=1);

namespace Capell\ThemeStudio\CallOut\View\Components;

use Capell\ThemeStudio\CallOut\Support\BookingsAvailability;
use Capell\ThemeStudio\CallOut\Support\FormBuilderAvailability;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Provider-resolved optional-integration fallback for Call Out's quote path:
 * a real PHP class component (not a public `@php` Blade block — see
 * `AssertsPublicThemeOutputSafety`'s `@php` policy) that decides, at render
 * time, which of three quote-request paths the theme's Blade markup should
 * present:
 *
 * - `form-builder` installed: a real embedded quote-request form.
 * - `bookings` installed (and no form-builder): a booking-slot picker CTA.
 * - neither installed: a plain `mailto:`/`tel:` fallback card.
 *
 * The Blade view only ever branches on the plain `$path` string this
 * component resolves; it never calls `CapellCore::isPackageInstalled()` or
 * any package-detection API itself.
 */
final class QuoteRequestPath extends Component
{
    public string $path;

    public function __construct(
        public readonly string $phoneNumber,
        public readonly string $emailAddress,
        public readonly string $quoteUrl = '#',
    ) {
        $this->path = match (true) {
            FormBuilderAvailability::check() => 'form',
            BookingsAvailability::check() => 'bookings',
            default => 'mailto',
        };
    }

    public function render(): View
    {
        return view('capell-theme-call-out::components.quote-request-path');
    }
}
