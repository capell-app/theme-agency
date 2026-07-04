@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-dense-news-analysis::sections.navigation.brand')));
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-dense-news-analysis::sections.navigation.latest'), 'url' => '/'],
        ['label' => __('capell-theme-dense-news-analysis::sections.navigation.architecture'), 'url' => '/'],
        ['label' => __('capell-theme-dense-news-analysis::sections.navigation.interiors'), 'url' => '/'],
        ['label' => __('capell-theme-dense-news-analysis::sections.navigation.design'), 'url' => '/'],
        ['label' => __('capell-theme-dense-news-analysis::sections.navigation.fashion'), 'url' => '/'],
        ['label' => __('capell-theme-dense-news-analysis::sections.navigation.art'), 'url' => '/'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', __('capell-theme-dense-news-analysis::sections.navigation.subscribe'));
    $ctaUrl = data_get($section, 'ctaUrl', '/');
@endphp

<header class="dnews-masthead dnews-section">
    <div class="dnews-section-inner">
        <p class="dnews-masthead-top dnews-meta">
            <span>
                {{ __('capell-theme-dense-news-analysis::sections.navigation.tagline') }}
            </span>
            <span class="dnews-masthead-live">
                {{ __('capell-theme-dense-news-analysis::sections.navigation.live') }}
            </span>
        </p>

        <div class="dnews-masthead-brand">
            <a
                class="dnews-masthead-wordmark"
                href="/"
            >
                {{ $brand }}
            </a>
            <p class="dnews-meta">
                {{ __('capell-theme-dense-news-analysis::sections.navigation.edition') }}
            </p>
        </div>

        <nav
            class="dnews-masthead-nav"
            aria-label="{{ __('capell-theme-dense-news-analysis::sections.navigation.aria_label') }}"
        >
            @foreach ($links as $link)
                <a
                    href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                >
                    {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                </a>
            @endforeach

            <a
                class="dnews-masthead-cta"
                href="{{ $ctaUrl }}"
            >
                {{ $ctaLabel }}
            </a>
        </nav>
    </div>
</header>
