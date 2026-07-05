@php
    $brand = data_get($section, 'brand', data_get($section, 'brandName', __('capell-theme-field-guide::sections.navigation.brand')));
    $links = collect(data_get($section, 'items', [
        ['label' => __('capell-theme-field-guide::sections.navigation.types'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.navigation.styles'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.navigation.colours'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.navigation.industries'), 'url' => '#taxonomy-navigation'],
        ['label' => __('capell-theme-field-guide::sections.navigation.picks'), 'url' => '#editor-picks'],
        ['label' => __('capell-theme-field-guide::sections.navigation.latest'), 'url' => '#latest-designs'],
    ]))->filter(fn (mixed $link): bool => filled(data_get($link, 'label', data_get($link, 'title'))))->values();
    $ctaLabel = data_get($section, 'ctaLabel', __('capell-theme-field-guide::sections.navigation.cta'));
    $ctaUrl = data_get($section, 'ctaUrl', '#cta');
@endphp

<header class="fga-topbar">
    <nav
        class="fga-topbar-inner"
        aria-label="{{ $brand }}"
    >
        <a
            class="fga-topbar-brand"
            href="/"
        >
            <span class="fga-topbar-wordmark">{{ $brand }}</span>
            <span class="fga-topbar-index-count">
                {{ __('capell-theme-field-guide::sections.navigation.index_count') }}
            </span>
        </a>

        <ul class="fga-topbar-nav">
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

        <a
            class="fga-button fga-topbar-cta"
            href="{{ $ctaUrl }}"
        >
            {{ $ctaLabel }}
        </a>
    </nav>
</header>
