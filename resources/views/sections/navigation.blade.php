@php
    $currentPath = '/' . ltrim(request()->path(), '/');
    $normalisePath = static function (mixed $url): string {
        if (! is_string($url) || trim($url) === '') {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) ? '/' . ltrim($path, '/') : '';
    };
    $links = collect(data_get($section, 'items', [
        ['label' => __('capell-theme-agency::sections.navigation.latest'), 'url' => '/'],
        ['label' => __('capell-theme-agency::sections.navigation.product'), 'url' => '/'],
        ['label' => __('capell-theme-agency::sections.navigation.design'), 'url' => '/'],
        ['label' => __('capell-theme-agency::sections.navigation.advice'), 'url' => '/'],
        ['label' => __('capell-theme-agency::sections.navigation.events'), 'url' => '/'],
        ['label' => __('capell-theme-agency::sections.navigation.company'), 'url' => '/'],
    ]))
        ->filter(
            static fn (mixed $link): bool => is_array($link)
                && filled(data_get($link, 'label', data_get($link, 'title')))
                && filled(data_get($link, 'url', data_get($link, 'href'))),
        )
        ->map(function (array $link) use ($currentPath, $normalisePath): array {
            $url = data_get($link, 'url', data_get($link, 'href', ''));

            return [
                ...$link,
                'active' => (bool) data_get($link, 'active', false)
                    || $normalisePath($url) === $currentPath,
            ];
        })
        ->values();
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = data_get($section, 'ctaUrl', data_get($section, 'cta_url', '/'));
    $brandUrl = data_get($section, 'brandUrl', '/');
@endphp

<nav
    class="ppc-masthead"
    aria-label="{{ __('capell-theme-agency::sections.navigation.aria_label') }}"
>
    <div class="ppc-masthead-inner">
        <a
            class="ppc-masthead-brand"
            href="{{ $brandUrl }}"
        >
            <span class="ppc-masthead-wordmark">
                {{ data_get($section, 'brand', __('capell-theme-agency::sections.navigation.brand')) }}
            </span>
            <span class="ppc-masthead-tagline">
                {{ data_get($section, 'tagline', __('capell-theme-agency::sections.navigation.tagline')) }}
            </span>
        </a>

        <ul class="ppc-masthead-nav capell-desktop-nav">
            @foreach ($links as $link)
                <li>
                    <a
                        href="{{ data_get($link, 'url', data_get($link, 'href', '/')) }}"
                        @if ((bool) data_get($link, 'active', false)) aria-current="page" @endif
                    >
                        {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                    </a>
                </li>
            @endforeach

            @if (filled($ctaLabel))
                <li>
                    <a
                        class="ppc-masthead-cta"
                        href="{{ $ctaUrl }}"
                    >
                        {{ $ctaLabel }}
                    </a>
                </li>
            @endif
        </ul>

        {!! view('capell-theme-foundation::theme.partials.mobile-navigation', [
            'links' => $links,
            'ctaLabel' => $ctaLabel,
            'ctaUrl' => $ctaUrl,
        ])->render() !!}
    </div>
</nav>
