@php
    use Capell\Frontend\Facades\Frontend;

    $site = Frontend::site();
    $layout = Frontend::layout();
    $siteTitle = $site?->translation?->title ?? $site?->name;
@endphp

{{--
    Liquid Glass's own footer chrome, wired through `Theme::meta.footer_file`
    (see `layout/index.blade.php`'s `<x-dynamic-component>` fallback and
    `LiquidGlassThemeServiceProvider::registerLayoutAreas()`'s docblock).

    Renders the shared `footer` layout-builder area so any widgets an admin
    places there appear here, in the same translucent glass idiom as the
    rest of the theme.
--}}
<footer
    class="glass-surface border-t border-(--theme-foreground)/10 bg-(--theme-surface)/70 px-5 py-12 sm:px-6 lg:px-8"
>
    <h2 class="sr-only">
        {{ __('capell-theme-liquid-glass::generic.footer') }}
    </h2>

    <div class="mx-auto max-w-7xl">
        <p class="text-2xl font-black text-(--theme-foreground)">
            {{ $siteTitle }}
        </p>

        <div class="mt-8">
            <x-capell::layout.area
                area="footer"
                :layout="$layout"
            />
        </div>
    </div>
</footer>
