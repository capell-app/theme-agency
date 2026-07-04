@php
    $links = data_get($section, 'items', [
        ['label' => __('capell-theme-creative-culture-editorial::sections.navigation.product'), 'url' => '/#project-stories'],
        ['label' => __('capell-theme-creative-culture-editorial::sections.navigation.design'), 'url' => '/#opinion-block'],
        ['label' => __('capell-theme-creative-culture-editorial::sections.navigation.advice'), 'url' => '/#advice-culture'],
        ['label' => __('capell-theme-creative-culture-editorial::sections.navigation.events'), 'url' => '/#events-tags'],
        ['label' => __('capell-theme-creative-culture-editorial::sections.navigation.company'), 'url' => '/theme-creative-culture-editorial-directory'],
    ]);
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/#newsletter'));
@endphp

<nav
    class="cce-masthead"
    aria-label="{{ __('capell-theme-creative-culture-editorial::sections.navigation.aria_label') }}"
>
    <div class="cce-masthead-inner">
        <a
            class="cce-masthead-brand"
            href="/"
        >
            <span
                class="cce-masthead-mark"
                aria-hidden="true"
            >
                {{ mb_substr(trim((string) data_get($section, 'brand', __('capell-theme-creative-culture-editorial::sections.navigation.brand'))), 0, 1) }}
            </span>
            <span class="cce-masthead-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-creative-culture-editorial::sections.navigation.brand')) }}
            </span>
        </a>

        <ul class="cce-masthead-nav">
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
                        class="cce-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>
    </div>
</nav>
