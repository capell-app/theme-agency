@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-experimental-directory::sections.navigation.today'), 'url' => '/'],
        ['label' => __('capell-theme-experimental-directory::sections.navigation.submissions'), 'url' => '/theme-experimental-directory-directory'],
        ['label' => __('capell-theme-experimental-directory::sections.navigation.winners'), 'url' => '/theme-experimental-directory-cta'],
        ['label' => __('capell-theme-experimental-directory::sections.navigation.profiles'), 'url' => '/theme-experimental-directory-detail'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/theme-experimental-directory-contact'));
@endphp

<nav
    class="exd-masthead"
    aria-label="{{ __('capell-theme-experimental-directory::sections.navigation.aria_label') }}"
>
    <div class="exd-masthead-inner">
        <a
            class="exd-masthead-brand"
            href="/"
        >
            {{ data_get($section, 'brand', __('capell-theme-experimental-directory::sections.navigation.brand')) }}
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
