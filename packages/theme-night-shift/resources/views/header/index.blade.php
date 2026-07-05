@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Night Shift's own header chrome, wired through `Theme::meta.header_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback and
    `NightShiftThemeServiceProvider::registerLayoutAreas()`'s docblock for
    why this is the real extension point rather than a Blade view-chain
    override of `capell::header.index`, which is a class-aliased component
    and cannot be overridden by view path alone) — mirrors
    `capell-theme-liquid-glass::header.index` exactly.

    Renders the shared `header` layout-builder area so any widgets an admin
    places there (e.g. a navigation widget) appear here, plus the theme's own
    brand mark, in the same near-black product-UI idiom as the rest of the
    theme.

    `dps-shell` is carried on this element (rather than a page-level wrapper,
    which no longer exists now that this theme is definition-only — see
    `NightShiftThemeServiceProvider`) so the `--dps-*` token derivations and
    `@container dps-tokens style(--theme-*: ...)` rules in
    `resources/css/theme-night-shift.css` still resolve here, without leaking
    those theme-specific declarations onto the shared `.site-theme-shell`
    wrapper every other layout-native theme's chrome also renders inside.
--}}
<header class="dps-shell dps-navbar">
    <div class="dps-navbar-inner">
        <a
            href="{{ $site?->siteDomain?->url ?? '/' }}"
            class="dps-navbar-brand"
        >
            <span
                class="dps-navbar-mark"
                aria-hidden="true"
            ></span>
            <span class="dps-navbar-wordmark"> {{ $siteTitle }} </span>
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
