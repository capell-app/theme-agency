@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-gold-rush::sections.navigation.winners'), 'url' => '/'],
        ['label' => __('capell-theme-gold-rush::sections.navigation.nominees'), 'url' => '/'],
        ['label' => __('capell-theme-gold-rush::sections.navigation.scores'), 'url' => '/'],
        ['label' => __('capell-theme-gold-rush::sections.navigation.vote'), 'url' => '/'],
        ['label' => __('capell-theme-gold-rush::sections.navigation.submit'), 'url' => '/'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="sbs-masthead"
    aria-label="{{ __('capell-theme-gold-rush::sections.navigation.aria_label') }}"
>
    <div class="sbs-masthead-inner">
        <a
            class="sbs-masthead-brand"
            href="/"
        >
            <span class="sbs-masthead-wordmark">
                {{ data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-gold-rush::sections.navigation.brand'))) }}
            </span>
            <span class="sbs-masthead-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-gold-rush::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="sbs-masthead-nav">
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
                        class="sbs-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
