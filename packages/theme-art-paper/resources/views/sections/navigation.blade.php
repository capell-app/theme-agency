@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-art-paper::sections.navigation.latest'), 'url' => '/'],
        ['label' => __('capell-theme-art-paper::sections.navigation.architecture'), 'url' => '/'],
        ['label' => __('capell-theme-art-paper::sections.navigation.design'), 'url' => '/'],
        ['label' => __('capell-theme-art-paper::sections.navigation.interiors'), 'url' => '/'],
        ['label' => __('capell-theme-art-paper::sections.navigation.fashion'), 'url' => '/'],
        ['label' => __('capell-theme-art-paper::sections.navigation.art'), 'url' => '/'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="dlm-masthead"
    aria-label="{{ __('capell-theme-art-paper::sections.navigation.aria_label') }}"
>
    <div class="dlm-masthead-inner">
        <a
            class="dlm-masthead-brand"
            href="/"
        >
            <span class="dlm-masthead-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-art-paper::sections.navigation.brand')) }}
            </span>
            <span class="dlm-masthead-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-art-paper::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="dlm-masthead-nav">
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
                        class="dlm-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
