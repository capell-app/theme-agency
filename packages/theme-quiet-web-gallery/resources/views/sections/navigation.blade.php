@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-quiet-web-gallery::sections.navigation.browse'), 'url' => '#browse-panels'],
        ['label' => __('capell-theme-quiet-web-gallery::sections.navigation.latest'), 'url' => '#latest-showcase'],
        ['label' => __('capell-theme-quiet-web-gallery::sections.navigation.categories'), 'url' => '#style-type-categories'],
        ['label' => __('capell-theme-quiet-web-gallery::sections.navigation.best_of'), 'url' => '#random-best-of'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="qwg-topbar"
    aria-label="{{ __('capell-theme-quiet-web-gallery::sections.navigation.aria_label') }}"
>
    <div class="qwg-topbar-inner">
        <a
            class="qwg-topbar-brand"
            href="/"
        >
            <span class="qwg-topbar-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-quiet-web-gallery::sections.navigation.brand')) }}
            </span>
            <span class="qwg-topbar-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-quiet-web-gallery::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="qwg-topbar-nav">
            @foreach ($links as $link)
                <li>
                    <a
                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                    >
                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                    </a>
                </li>
            @endforeach

            @if (filled($ctaLabel))
                <li>
                    <a
                        class="qwg-topbar-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
