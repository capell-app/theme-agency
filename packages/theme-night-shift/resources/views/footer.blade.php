@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Night Shift's own footer chrome, wired through `Theme::meta.footer_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback and
    `NightShiftThemeServiceProvider::registerLayoutAreas()`'s docblock) —
    mirrors `capell-theme-liquid-glass::footer` exactly.

    Renders the shared `footer` layout-builder area so any widgets an admin
    places there appear here, in the same near-black product-UI idiom as the
    rest of the theme.

    `dps-shell` is carried on this element for the same reason documented in
    `resources/views/header/index.blade.php` — it is the real, rendered
    root that needs the `--dps-*` token derivations and
    `@container dps-tokens style(--theme-*: ...)` rules, now that no
    page-level wrapper carries that class any more.
--}}
<footer
    id="footer"
    class="dps-shell dps-section dps-footer"
>
    <h2 class="sr-only">
        {{ __('capell-theme-night-shift::generic.footer') }}
    </h2>

    <div class="dps-section-inner">
        <div class="dps-footer-brand">
            <p class="dps-footer-wordmark">{{ $siteTitle }}</p>
        </div>

        <div class="mt-8">
            <x-capell::layout.area
                area="footer"
                :layout="$layout"
            />
        </div>
    </div>
</footer>
