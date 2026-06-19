@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-quiet-luxury-retail::sections.navigation.shop'), 'url' => '/'],
        ['label' => __('capell-theme-quiet-luxury-retail::sections.navigation.skin'), 'url' => '/'],
        ['label' => __('capell-theme-quiet-luxury-retail::sections.navigation.fragrance'), 'url' => '/'],
        ['label' => __('capell-theme-quiet-luxury-retail::sections.navigation.rituals'), 'url' => '/'],
        ['label' => __('capell-theme-quiet-luxury-retail::sections.navigation.stores'), 'url' => '/'],
        ['label' => __('capell-theme-quiet-luxury-retail::sections.navigation.journal'), 'url' => '/'],
    ]);
@endphp

<nav class="luxury-section">
    <div
        class="luxury-section-inner"
        style="padding-block: 1rem"
    >
        <div
            class="luxury-grid"
            style="align-items: center"
        >
            <strong>
                {{ data_get($section, 'brand', __('capell-theme-quiet-luxury-retail::sections.navigation.brand')) }}
            </strong>
            <div class="luxury-grid">
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
