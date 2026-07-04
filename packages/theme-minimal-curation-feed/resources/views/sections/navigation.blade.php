@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-minimal-curation-feed::sections.navigation.latest'), 'url' => '#curation-feed'],
        ['label' => __('capell-theme-minimal-curation-feed::sections.navigation.best_of'), 'url' => '#best-of-views'],
        ['label' => __('capell-theme-minimal-curation-feed::sections.navigation.apps'), 'url' => '#app-website-icons'],
        ['label' => __('capell-theme-minimal-curation-feed::sections.navigation.websites'), 'url' => '#app-website-icons'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '#newsletter'));
@endphp

<nav
    class="mcf-topbar"
    aria-label="{{ __('capell-theme-minimal-curation-feed::sections.navigation.aria_label') }}"
>
    <div class="mcf-topbar-inner">
        <a
            class="mcf-topbar-brand"
            href="/"
        >
            {{ data_get($section, 'brand', __('capell-theme-minimal-curation-feed::sections.navigation.brand')) }}
        </a>

        <ul class="mcf-topbar-nav">
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
                        class="mcf-topbar-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
