<?php

declare(strict_types=1);

use Capell\Core\Facades\CapellCore;
use Capell\ThemeStudio\CallOut\Support\BlogAvailability;
use Capell\ThemeStudio\CallOut\Support\BookingsAvailability;
use Capell\ThemeStudio\CallOut\Support\FormBuilderAvailability;
use Capell\ThemeStudio\CallOut\View\Components\QuoteRequestPath;

/**
 * Provider-resolved optional-integration fallbacks (Wave 5 spec step 4):
 * form-builder falls back to a mailto card, bookings falls back to a phone
 * CTA, blog falls back to hiding the rail — each resolved from real PHP
 * (never `CapellCore::isPackageInstalled()` inside a public `@php` Blade
 * block; see `AssertsPublicThemeOutputSafety`'s `@php` policy).
 */
it('reports form-builder unavailable when the package is not installed', function (): void {
    CapellCore::clearPackages();

    expect(FormBuilderAvailability::check())->toBeFalse();

    CapellCore::clearPackages();
});

it('reports form-builder available once the package is installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled('capell-app/form-builder');

    expect(FormBuilderAvailability::check())->toBeTrue();

    CapellCore::clearPackages();
});

it('reports bookings unavailable when the package is not installed', function (): void {
    CapellCore::clearPackages();

    expect(BookingsAvailability::check())->toBeFalse();

    CapellCore::clearPackages();
});

it('reports bookings available once the package is installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled('capell-app/bookings');

    expect(BookingsAvailability::check())->toBeTrue();

    CapellCore::clearPackages();
});

it('reports blog unavailable when the package is not installed', function (): void {
    CapellCore::clearPackages();

    expect(BlogAvailability::check())->toBeFalse();

    CapellCore::clearPackages();
});

it('resolves the quote-request path to mailto when neither optional package is installed', function (): void {
    CapellCore::clearPackages();

    $component = new QuoteRequestPath(phoneNumber: '0800 555 0192', emailAddress: 'quotes@example.test');

    expect($component->path)->toBe('mailto');

    CapellCore::clearPackages();
});

it('resolves the quote-request path to bookings when only bookings is installed', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled('capell-app/bookings');

    $component = new QuoteRequestPath(phoneNumber: '0800 555 0192', emailAddress: 'quotes@example.test');

    expect($component->path)->toBe('bookings');

    CapellCore::clearPackages();
});

it('resolves the quote-request path to form when form-builder is installed, even alongside bookings', function (): void {
    CapellCore::clearPackages();
    CapellCore::forcePackageInstalled('capell-app/bookings');
    CapellCore::forcePackageInstalled('capell-app/form-builder');

    $component = new QuoteRequestPath(phoneNumber: '0800 555 0192', emailAddress: 'quotes@example.test');

    expect($component->path)->toBe('form');

    CapellCore::clearPackages();
});
