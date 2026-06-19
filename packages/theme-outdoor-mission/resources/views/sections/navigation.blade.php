@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-outdoor-mission::sections.navigation.shop'), 'url' => '/'],
        ['label' => __('capell-theme-outdoor-mission::sections.navigation.sports'), 'url' => '/'],
        ['label' => __('capell-theme-outdoor-mission::sections.navigation.stories'), 'url' => '/'],
        ['label' => __('capell-theme-outdoor-mission::sections.navigation.action'), 'url' => '/'],
        ['label' => __('capell-theme-outdoor-mission::sections.navigation.repair'), 'url' => '/'],
    ]);
@endphp

<nav class="outdoor-section">
    <div
        class="outdoor-section-inner"
        style="padding-block: 1rem"
    >
        <div
            class="outdoor-grid"
            style="align-items: center"
        >
            <strong>
                {{ data_get($section, 'brand', __('capell-theme-outdoor-mission::sections.navigation.brand')) }}
            </strong>
            <div class="outdoor-grid">
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
