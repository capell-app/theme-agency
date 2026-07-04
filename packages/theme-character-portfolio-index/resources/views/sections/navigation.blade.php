@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-character-portfolio-index::sections.navigation.portfolios'), 'url' => '#curated-grid'],
        ['label' => __('capell-theme-character-portfolio-index::sections.navigation.categories'), 'url' => '#category-tabs'],
        ['label' => __('capell-theme-character-portfolio-index::sections.navigation.standouts'), 'url' => '#standout-notes'],
        ['label' => __('capell-theme-character-portfolio-index::sections.navigation.creators'), 'url' => '#creator-summary'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="cpi-masthead"
    aria-label="{{ __('capell-theme-character-portfolio-index::sections.navigation.aria_label') }}"
>
    <div class="cpi-masthead-inner">
        <a
            class="cpi-masthead-brand"
            href="/"
        >
            <span class="cpi-masthead-wordmark">
                {{ data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-character-portfolio-index::sections.navigation.brand'))) }}
            </span>
            <span class="cpi-masthead-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-character-portfolio-index::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="cpi-masthead-nav">
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
                        class="cpi-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
