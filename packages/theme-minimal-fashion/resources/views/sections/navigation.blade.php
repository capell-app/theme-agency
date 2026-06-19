@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-minimal-fashion::sections.navigation.shop'), 'url' => '/'],
        ['label' => __('capell-theme-minimal-fashion::sections.navigation.women'), 'url' => '/'],
        ['label' => __('capell-theme-minimal-fashion::sections.navigation.men'), 'url' => '/'],
        ['label' => __('capell-theme-minimal-fashion::sections.navigation.lookbook'), 'url' => '/'],
        ['label' => __('capell-theme-minimal-fashion::sections.navigation.care'), 'url' => '/'],
        ['label' => __('capell-theme-minimal-fashion::sections.navigation.stores'), 'url' => '/'],
    ]);
@endphp

<nav class="fashion-section">
    <div
        class="fashion-section-inner"
        style="padding-block: 1rem"
    >
        <div
            class="fashion-grid"
            style="align-items: center"
        >
            <strong>
                {{ data_get($section, 'brand', __('capell-theme-minimal-fashion::sections.navigation.brand')) }}
            </strong>
            <div class="fashion-grid">
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
