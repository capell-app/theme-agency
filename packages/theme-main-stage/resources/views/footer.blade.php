@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Main Stage's own footer chrome, wired through `Theme::meta.footer_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback and
    `MainStageThemeServiceProvider::registerLayoutAreas()`'s docblock) —
    mirrors `capell-theme-night-shift::footer` exactly.

    Renders the shared `footer` layout-builder area so any widgets an admin
    places there appear here.

    `mst-shell` is carried on this element for the same reason documented in
    `resources/views/header/index.blade.php`.
--}}
<footer
    id="footer"
    class="mst-shell mst-section mst-footer"
>
    <h2 class="sr-only">{{ __('capell-theme-main-stage::generic.footer') }}</h2>

    <div class="mst-section-inner">
        <div class="mst-footer-brand">
            <p class="mst-footer-wordmark">{{ $siteTitle }}</p>
        </div>

        <div class="mt-8">
            <x-capell::layout.area
                area="footer"
                :layout="$layout"
            />
        </div>
    </div>
</footer>
