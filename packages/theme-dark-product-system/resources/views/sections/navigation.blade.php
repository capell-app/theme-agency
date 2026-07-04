@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-dark-product-system::sections.navigation.product'), 'url' => '/#workflow-rails'],
        ['label' => __('capell-theme-dark-product-system::sections.navigation.automation'), 'url' => '/#agents-automation'],
        ['label' => __('capell-theme-dark-product-system::sections.navigation.roadmap'), 'url' => '/#planning-roadmap'],
        ['label' => __('capell-theme-dark-product-system::sections.navigation.security'), 'url' => '/#security-proof'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
@endphp

<nav
    class="dps-navbar"
    aria-label="{{ __('capell-theme-dark-product-system::sections.navigation.aria_label') }}"
>
    <div class="dps-navbar-inner">
        <a
            class="dps-navbar-brand"
            href="/"
        >
            <span
                class="dps-navbar-mark"
                aria-hidden="true"
            ></span>
            <span class="dps-navbar-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-dark-product-system::sections.navigation.brand')) }}
            </span>
        </a>

        <ul class="dps-navbar-links">
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

        <div class="dps-navbar-actions">
            <a
                class="dps-navbar-signin"
                href="/#security-proof"
            >
                {{ __('capell-theme-dark-product-system::sections.navigation.signin') }}
            </a>
            @if (filled($ctaLabel))
                <a
                    class="dps-button"
                    href="{{ $ctaUrl }}"
                >
                    {{ $ctaLabel }}
                </a>
            @endif
        </div>
    </div>
</nav>
