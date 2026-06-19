@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-editorial-crm::sections.navigation.latest'), 'url' => '/'],
        ['label' => __('capell-theme-editorial-crm::sections.navigation.product'), 'url' => '/'],
        ['label' => __('capell-theme-editorial-crm::sections.navigation.design'), 'url' => '/'],
        ['label' => __('capell-theme-editorial-crm::sections.navigation.advice'), 'url' => '/'],
        ['label' => __('capell-theme-editorial-crm::sections.navigation.events'), 'url' => '/'],
        ['label' => __('capell-theme-editorial-crm::sections.navigation.company'), 'url' => '/'],
    ]);
@endphp

<nav class="editorial-section">
    <div
        class="editorial-section-inner"
        style="padding-block: 1rem"
    >
        <div
            class="editorial-grid"
            style="align-items: center"
        >
            <strong>
                {{ data_get($section, 'brand', __('capell-theme-editorial-crm::sections.navigation.brand')) }}
            </strong>
            <div class="editorial-grid">
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
