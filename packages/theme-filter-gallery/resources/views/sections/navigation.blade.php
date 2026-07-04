@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-filter-gallery::sections.navigation.brand')));
    $links = collect(data_get($section, 'items', [
        ['label' => __('capell-theme-filter-gallery::sections.navigation.types'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-filter-gallery::sections.navigation.styles'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-filter-gallery::sections.navigation.colours'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-filter-gallery::sections.navigation.industries'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-filter-gallery::sections.navigation.picks'), 'url' => '#editor-picks'],
        ['label' => __('capell-theme-filter-gallery::sections.navigation.latest'), 'url' => '#latest-designs'],
    ]))->filter(fn (mixed $link): bool => filled(data_get($link, 'label', data_get($link, 'title'))))->values();
    $ctaLabel = data_get($section, 'ctaLabel', __('capell-theme-filter-gallery::sections.navigation.cta'));
    $ctaUrl = data_get($section, 'ctaUrl', '#cta');
@endphp

<header class="fga-topbar">
    <nav
        class="fga-topbar-inner"
        aria-label="{{ $brand }}"
    >
        <a
            class="fga-topbar-brand"
            href="/"
        >
            <span class="fga-topbar-wordmark">{{ $brand }}</span>
            <span class="fga-topbar-index-count">
                {{ __('capell-theme-filter-gallery::sections.navigation.index_count') }}
            </span>
        </a>

        <ul class="fga-topbar-nav">
            @foreach ($links as $link)
                <li>
                    <a
                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                    >
                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                    </a>
                </li>
            @endforeach
        </ul>

        <a
            class="fga-button fga-topbar-cta"
            href="{{ $ctaUrl }}"
        >
            {{ $ctaLabel }}
        </a>
    </nav>
</header>
