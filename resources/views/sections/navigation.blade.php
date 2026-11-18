@php
    use Capell\Core\Support\Security\PublicUrlSanitizer;

    $currentPath = '/' . ltrim(request()->path(), '/');
    $normalisePath = static function (mixed $url): string {
        if (! is_string($url) || trim($url) === '') {
            return '';
        }

        $path = parse_url($url, PHP_URL_PATH);

        return is_string($path) ? '/' . ltrim($path, '/') : '';
    };
    $links = collect(data_get($section, 'items', []))
        ->filter(
            static fn (mixed $link): bool => is_array($link)
                && filled(data_get($link, 'label', data_get($link, 'title')))
                && filled(PublicUrlSanitizer::sanitize(data_get($link, 'url', data_get($link, 'href')))),
        )
        ->map(function (array $link) use ($currentPath, $normalisePath): array {
            $url = data_get($link, 'url', data_get($link, 'href', ''));
            $safeUrl = PublicUrlSanitizer::sanitize($url);

            return [
                ...$link,
                'url' => $safeUrl,
                'href' => $safeUrl,
                'active' => (bool) data_get($link, 'active', false)
                    || $normalisePath($url) === $currentPath,
            ];
        })
        ->values();
    $brand = data_get($section, 'brand', data_get($section, 'brandName'));
    $tagline = data_get($section, 'tagline');
    $ctaLabel = data_get($section, 'ctaLabel', data_get($section, 'cta_label'));
    $ctaUrl = PublicUrlSanitizer::sanitize(data_get($section, 'ctaUrl', data_get($section, 'cta_url')));
    $hasCta = filled($ctaLabel) && filled($ctaUrl);
    $brandUrl = PublicUrlSanitizer::sanitize(data_get($section, 'brandUrl', '/')) ?? '/';
@endphp

@if (filled($brand) || $links->isNotEmpty() || $hasCta)
    <nav
        class="ppc-masthead"
        aria-label="{{ __('capell-theme-agency::sections.navigation.aria_label') }}"
    >
        <div class="ppc-masthead-inner">
            @if (filled($brand))
                <a
                    class="ppc-masthead-brand"
                    href="{{ $brandUrl }}"
                >
                    <span class="ppc-masthead-wordmark"> {{ $brand }} </span>
                    @if (filled($tagline))
                        <span class="ppc-masthead-tagline">
                            {{ $tagline }}
                        </span>
                    @endif
                </a>
            @endif

            @if ($links->isNotEmpty() || $hasCta)
                <ul class="ppc-masthead-nav capell-desktop-nav">
                    @foreach ($links as $link)
                        <li>
                            <a
                                href="{{ data_get($link, 'url') }}"
                                @if ((bool) data_get($link, 'active', false)) aria-current="page" @endif
                            >
                                {{ data_get($link, 'label', data_get($link, 'title', '')) }}
                            </a>
                        </li>
                    @endforeach

                    @if ($hasCta)
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

                {!!
                    view('capell-theme-foundation::theme.partials.mobile-navigation', [
                        'links' => $links,
                        'ctaLabel' => $hasCta ? $ctaLabel : null,
                        'ctaUrl' => $hasCta ? $ctaUrl : null,
                    ])->render()
                !!}
            @endif
        </div>
    </nav>
@endif
