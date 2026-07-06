@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Reading Room's own footer chrome, wired through `Theme::meta.footer_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback) —
    mirrors `capell-theme-night-shift::footer` exactly.

    Renders the shared `footer` layout-builder area so any widgets an admin
    places there appear here, in the same paper-light reading idiom as the
    rest of the theme.
--}}
<footer
    id="footer"
    class="rr-shell rr-section rr-footer"
>
    <h2 class="sr-only">
        {{ __('capell-theme-reading-room::generic.footer') }}
    </h2>

    <div class="rr-section-inner">
        <div class="rr-footer-brand">
            <p class="rr-footer-wordmark">{{ $siteTitle }}</p>
        </div>

        <div class="mt-8">
            <x-capell::layout.area
                area="footer"
                :layout="$layout"
            />
        </div>
    </div>
</footer>
