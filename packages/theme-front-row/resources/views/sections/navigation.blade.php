@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-front-row::sections.navigation.latest'), 'url' => '/'],
        ['label' => __('capell-theme-front-row::sections.navigation.product'), 'url' => '/'],
        ['label' => __('capell-theme-front-row::sections.navigation.design'), 'url' => '/'],
        ['label' => __('capell-theme-front-row::sections.navigation.advice'), 'url' => '/'],
        ['label' => __('capell-theme-front-row::sections.navigation.events'), 'url' => '/'],
        ['label' => __('capell-theme-front-row::sections.navigation.company'), 'url' => '/'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="ppc-masthead"
    aria-label="{{ __('capell-theme-front-row::sections.navigation.aria_label') }}"
>
    <div class="ppc-masthead-inner">
        <a
            class="ppc-masthead-brand"
            href="/"
        >
            <span class="ppc-masthead-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-front-row::sections.navigation.brand')) }}
            </span>
            <span class="ppc-masthead-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-front-row::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="ppc-masthead-nav">
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
                        class="ppc-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
