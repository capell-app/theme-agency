@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-far-field::sections.navigation.brand')));
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-far-field::sections.navigation.latest'), 'url' => '/'],
        ['label' => __('capell-theme-far-field::sections.navigation.affairs'), 'url' => '/'],
        ['label' => __('capell-theme-far-field::sections.navigation.travel'), 'url' => '/'],
        ['label' => __('capell-theme-far-field::sections.navigation.culture'), 'url' => '/'],
        ['label' => __('capell-theme-far-field::sections.navigation.radio'), 'url' => '/'],
        ['label' => __('capell-theme-far-field::sections.navigation.shop'), 'url' => '/'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', '');
    $ctaUrl = data_get($section, 'ctaUrl', '#');
@endphp

<header class="gcm-masthead">
    <div class="gcm-masthead-inner">
        <div class="gcm-masthead-top">
            <p class="gcm-masthead-edition">
                {{ __('capell-theme-far-field::sections.navigation.edition') }}
            </p>
            <p class="gcm-masthead-tagline">
                {{ __('capell-theme-far-field::sections.navigation.tagline') }}
            </p>
        </div>

        <p class="gcm-masthead-brand">
            <a href="/">{{ $brand }}</a>
        </p>

        <nav
            class="gcm-masthead-nav"
            aria-label="{{ __('capell-theme-far-field::sections.navigation.aria_label') }}"
        >
            <ul>
                @foreach ($links as $link)
                    <li>
                        <a
                            href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                        >
                            {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                        </a>
                    </li>
                @endforeach

                @if ($ctaLabel !== '')
                    <li>
                        <a
                            class="gcm-masthead-cta"
                            href="{{ $ctaUrl }}"
                        >
                            {{ $ctaLabel }}
                        </a>
                    </li>
                @endif
            </ul>
        </nav>
    </div>
</header>
