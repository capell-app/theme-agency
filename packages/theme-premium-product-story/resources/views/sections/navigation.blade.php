@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-premium-product-story::sections.navigation.overview'), 'url' => '/'],
        ['label' => __('capell-theme-premium-product-story::sections.navigation.models'), 'url' => '/'],
        ['label' => __('capell-theme-premium-product-story::sections.navigation.features'), 'url' => '/'],
        ['label' => __('capell-theme-premium-product-story::sections.navigation.compare'), 'url' => '/'],
        ['label' => __('capell-theme-premium-product-story::sections.navigation.support'), 'url' => '/'],
        ['label' => __('capell-theme-premium-product-story::sections.navigation.buy'), 'url' => '/'],
    ]);
@endphp

<nav class="product-section">
    <div
        class="product-section-inner"
        style="padding-block: 1rem"
    >
        <div
            class="product-grid"
            style="align-items: center"
        >
            <strong>
                {{ data_get($section, 'brand', __('capell-theme-premium-product-story::sections.navigation.brand')) }}
            </strong>
            <div class="product-grid">
                @foreach ($links as $link)
                    <a
                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                    >
                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>
</nav>
