@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-wild-card::sections.navigation.today'), 'url' => '/'],
        ['label' => __('capell-theme-wild-card::sections.navigation.submissions'), 'url' => '/theme-wild-card-directory'],
        ['label' => __('capell-theme-wild-card::sections.navigation.winners'), 'url' => '/theme-wild-card-cta'],
        ['label' => __('capell-theme-wild-card::sections.navigation.profiles'), 'url' => '/theme-wild-card-detail'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/theme-wild-card-contact'));
@endphp

<nav
    class="exd-masthead"
    aria-label="{{ __('capell-theme-wild-card::sections.navigation.aria_label') }}"
>
    <div class="exd-masthead-inner">
        <a
            class="exd-masthead-brand"
            href="/"
        >
            {{ data_get($section, 'brand', __('capell-theme-wild-card::sections.navigation.brand')) }}
        </a>

        <ul class="exd-masthead-nav">
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
                        class="exd-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
