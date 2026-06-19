@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-bold-sport-commerce::sections.navigation.shop'), 'url' => '/'],
        ['label' => __('capell-theme-bold-sport-commerce::sections.navigation.men'), 'url' => '/'],
        ['label' => __('capell-theme-bold-sport-commerce::sections.navigation.women'), 'url' => '/'],
        ['label' => __('capell-theme-bold-sport-commerce::sections.navigation.kids'), 'url' => '/'],
        ['label' => __('capell-theme-bold-sport-commerce::sections.navigation.teams'), 'url' => '/'],
        ['label' => __('capell-theme-bold-sport-commerce::sections.navigation.stories'), 'url' => '/'],
    ]);
@endphp

<nav class="sport-section">
    <div
        class="sport-section-inner"
        style="padding-block: 1rem"
    >
        <div
            class="sport-grid"
            style="align-items: center"
        >
            <strong>
                {{ data_get($section, 'brand', __('capell-theme-bold-sport-commerce::sections.navigation.brand')) }}
            </strong>
            <div class="sport-grid">
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
