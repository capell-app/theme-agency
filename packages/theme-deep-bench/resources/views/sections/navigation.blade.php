@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-deep-bench::sections.navigation.directory'), 'url' => '#portfolio-grid'],
        ['label' => __('capell-theme-deep-bench::sections.navigation.disciplines'), 'url' => '#role-filters'],
        ['label' => __('capell-theme-deep-bench::sections.navigation.lists'), 'url' => '#curated-lists'],
        ['label' => __('capell-theme-deep-bench::sections.navigation.resources'), 'url' => '#resume-resources'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label', __('capell-theme-deep-bench::sections.navigation.cta')));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', data_get($section, 'consultationUrl', '/')));
@endphp

<nav
    class="pfd-topbar"
    aria-label="{{ __('capell-theme-deep-bench::sections.navigation.aria_label') }}"
>
    <div class="pfd-topbar-inner">
        <a
            class="pfd-brand"
            href="/"
        >
            <span
                class="pfd-brand-mark"
                aria-hidden="true"
            ></span>
            <span class="pfd-brand-name">
                {{ data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-deep-bench::sections.navigation.brand'))) }}
            </span>
        </a>

        <ul class="pfd-topbar-nav">
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
                        class="pfd-topbar-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
