@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-one-page-showcase::sections.navigation.gallery'), 'url' => '/#one-page-grid'],
        ['label' => __('capell-theme-one-page-showcase::sections.navigation.templates'), 'url' => '/#templates-sections'],
        ['label' => __('capell-theme-one-page-showcase::sections.navigation.tools'), 'url' => '/#tools-sponsors'],
        ['label' => __('capell-theme-one-page-showcase::sections.navigation.resources'), 'url' => '/#build-resources'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="ops-nav"
    aria-label="{{ __('capell-theme-one-page-showcase::sections.navigation.aria_label') }}"
>
    <div class="ops-nav-inner">
        <a
            class="ops-brand"
            href="/"
        >
            <span
                class="ops-brand-mark"
                aria-hidden="true"
            ></span>
            {{ data_get($section, 'brand', __('capell-theme-one-page-showcase::sections.navigation.brand')) }}
        </a>

        <ul class="ops-nav-links">
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
                        class="ops-nav-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
