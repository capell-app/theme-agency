@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-off-grid::sections.navigation.index'), 'url' => '/'],
        ['label' => __('capell-theme-off-grid::sections.navigation.submit'), 'url' => '/'],
        ['label' => __('capell-theme-off-grid::sections.navigation.interviews'), 'url' => '/'],
        ['label' => __('capell-theme-off-grid::sections.navigation.archive'), 'url' => '/'],
        ['label' => __('capell-theme-off-grid::sections.navigation.rss'), 'url' => '/'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="rwi-masthead"
    aria-label="{{ __('capell-theme-off-grid::sections.navigation.aria_label') }}"
>
    <div class="rwi-masthead-inner">
        <a
            class="rwi-masthead-brand"
            href="/"
        >
            <span class="rwi-masthead-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-off-grid::sections.navigation.brand')) }}
            </span>
            <span class="rwi-masthead-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-off-grid::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="rwi-masthead-nav">
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
                        class="rwi-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
