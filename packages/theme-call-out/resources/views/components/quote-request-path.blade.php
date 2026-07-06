{{--
    Renders one of three provider-resolved quote-request paths, decided by
    `Capell\ThemeStudio\CallOut\View\Components\QuoteRequestPath`'s
    constructor (never by this Blade file, and never by an `@php` block —
    see that class's docblock). `$path` is one of "form", "bookings", or
    "mailto".
--}}
<div
    class="rco-quote-path-card"
    data-quote-path="{{ $path }}"
>
    @if ($path === 'form')
        <p class="rco-quote-path-note">
            {{ __('capell-theme-call-out::sections.quote_path.form_available') }}
        </p>
        <a
            href="{{ $quoteUrl }}"
            class="rco-btn rco-btn-primary"
        >
            {{ __('capell-theme-call-out::sections.quote_path.form_cta') }}
        </a>
    @elseif ($path === 'bookings')
        <p class="rco-quote-path-note">
            {{ __('capell-theme-call-out::sections.quote_path.bookings_available') }}
        </p>
        <a
            href="{{ $quoteUrl }}"
            class="rco-btn rco-btn-primary"
        >
            {{ __('capell-theme-call-out::sections.quote_path.bookings_cta') }}
        </a>
    @else
        <p class="rco-quote-path-note">
            {{ __('capell-theme-call-out::sections.quote_path.mailto_available') }}
        </p>
        <a
            href="tel:{{ $phoneNumber }}"
            class="rco-btn rco-btn-primary"
        >
            {{ __('capell-theme-call-out::sections.quote_path.call_cta') }} {{ $phoneNumber }}
        </a>
        <a
            href="mailto:{{ $emailAddress }}"
            class="rco-btn rco-btn-secondary"
        >
            {{ __('capell-theme-call-out::sections.quote_path.email_cta') }}
        </a>
    @endif
</div>
