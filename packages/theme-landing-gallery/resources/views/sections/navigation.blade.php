@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-landing-gallery::sections.navigation.websites'), 'url' => '/'],
        ['label' => __('capell-theme-landing-gallery::sections.navigation.templates'), 'url' => '/'],
        ['label' => __('capell-theme-landing-gallery::sections.navigation.categories'), 'url' => '/'],
        ['label' => __('capell-theme-landing-gallery::sections.navigation.partners'), 'url' => '/'],
        ['label' => __('capell-theme-landing-gallery::sections.navigation.pro'), 'url' => '/'],
    ]);
    $brandName = data_get($section, 'brandName', data_get($section, 'brand', __('capell-theme-landing-gallery::sections.navigation.brand')));
    $ctaLabel = data_get($section, 'ctaLabel', __('capell-theme-landing-gallery::sections.navigation.cta'));
    $ctaUrl = data_get($section, 'ctaUrl', '/');
@endphp

<nav class="lga-nav">
    <div class="lga-nav-inner">
        <a
            class="lga-brand"
            href="/"
        >
            <span
                class="lga-brand-mark"
                aria-hidden="true"
            ></span>
            {{ $brandName }}
        </a>
        <ul class="lga-nav-links">
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
            class="lga-button lga-button-small"
            href="{{ $ctaUrl }}"
        >
            {{ $ctaLabel }}
        </a>
    </div>
</nav>
