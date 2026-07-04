@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-motion-archive::sections.navigation.archive'), 'url' => '/#archive'],
        ['label' => __('capell-theme-motion-archive::sections.navigation.winners'), 'url' => '/#winner-list'],
        ['label' => __('capell-theme-motion-archive::sections.navigation.jury_scores'), 'url' => '/#jury-score-explainer'],
        ['label' => __('capell-theme-motion-archive::sections.navigation.credits'), 'url' => '/#media-credits'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', __('capell-theme-motion-archive::sections.navigation.submit'));
    $ctaUrl = data_get($section, 'ctaUrl', '/#newsletter');
    $brandName = data_get($section, 'brandName', data_get($section, 'brand', __('capell-theme-motion-archive::sections.navigation.brand')));
@endphp

<nav
    class="mva-section mva-nav"
    aria-label="Primary"
>
    <div class="mva-nav-inner">
        <a
            class="mva-brand"
            href="/"
        >
            {{ $brandName }}
        </a>
        <div class="mva-nav-links">
            @foreach ($links as $link)
                <a
                    href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                >
                    {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                </a>
            @endforeach

            <a
                class="mva-button"
                href="{{ $ctaUrl }}"
            >
                {{ $ctaLabel }}
            </a>
        </div>
    </div>
</nav>
