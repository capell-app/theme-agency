@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Main Stage's own header chrome, wired through `Theme::meta.header_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback and
    `MainStageThemeServiceProvider::registerLayoutAreas()`'s docblock) —
    mirrors `capell-theme-night-shift::header.index` exactly.

    Renders the shared `header` layout-builder area so any widgets an admin
    places there (e.g. a navigation widget) appear here, plus the theme's own
    poster-bold brand mark.

    `mst-shell` is carried on this element (rather than a page-level wrapper,
    which does not exist for a definition-only theme) so the `--mst-*` token
    derivations and `@container mst-tokens style(--theme-*: ...)` rules in
    `resources/css/theme-main-stage.css` resolve here, without leaking those
    theme-specific declarations onto the shared `.site-theme-shell` wrapper
    every other layout-native theme's chrome also renders inside.
--}}
<header class="mst-shell mst-navbar">
    <div class="mst-navbar-inner">
        <a
            href="{{ $site?->siteDomain?->url ?? '/' }}"
            class="mst-navbar-brand"
        >
            <span
                class="mst-navbar-mark"
                aria-hidden="true"
            ></span>
            <span class="mst-navbar-wordmark"> {{ $siteTitle }} </span>
        </a>

        <x-capell::layout.area
            area="header"
            :layout="$layout"
        />
    </div>
</header>

<span
    id="main-content"
    tabindex="-1"
></span>
