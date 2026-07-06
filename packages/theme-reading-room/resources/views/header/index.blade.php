@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Reading Room's own header chrome, wired through `Theme::meta.header_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback and
    `ReadingRoomThemeServiceProvider::registerLayoutAreas()`'s docblock) —
    mirrors `capell-theme-night-shift::header.index` exactly.

    Renders the shared `header` layout-builder area so any widgets an admin
    places there (e.g. a navigation widget) appear here, plus the theme's own
    quiet wordmark, in the same paper-light reading idiom as the rest of the
    theme.

    `rr-shell` is carried on this element (rather than a page-level wrapper,
    which does not exist since this theme is definition-only) so the
    `--rr-*` token derivations and `@container rr-tokens style(--theme-*: ...)`
    rules in `resources/css/theme-reading-room.css` still resolve here.
--}}
<header class="rr-shell rr-navbar">
    <div class="rr-navbar-inner">
        <a
            href="{{ $site?->siteDomain?->url ?? '/' }}"
            class="rr-navbar-brand"
        >
            <span class="rr-navbar-wordmark"> {{ $siteTitle }} </span>
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
