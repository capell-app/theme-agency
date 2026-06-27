{{--
    Shared vertical-theme chrome: top navigation bar.
    
    Rendered for every theme whose ServiceProvider points its 'navigation'
    ViewSectionRenderer at `capell-foundation-theme::theme.chrome.navigation`.
    Styled entirely with the `theme-chrome-nav__*` plain-CSS classes shipped in
    `resources/css/theme/chrome.css`, driven by the brand `--theme-*` custom
    properties, so each theme keeps its own colours/typography.
    
    Data shape (NavigationData): brandName, items[{label,url}], ctaLabel, ctaUrl.
--}}
<nav
    class="theme-chrome-nav"
    aria-label="{{ __('capell-foundation-theme::generic.main_navigation') }}"
>
    <div class="theme-chrome-nav__inner">
        <a
            href="/"
            class="theme-chrome-nav__brand"
        >
            <span class="theme-chrome-nav__brand-mark">
                {{ collect(explode(' ', trim((string) $section->brandName)))->filter()->map(fn (string $word): string => mb_strtoupper(mb_substr($word, 0, 1)))->take(2)->implode('') ?: 'C' }}
            </span>
            <span class="theme-chrome-nav__brand-name">
                {{ $section->brandName }}
            </span>
        </a>

        @if ($section->items !== [])
            <div class="theme-chrome-nav__links">
                @foreach ($section->items as $item)
                    @if (! empty($item['url']) && ! empty($item['label']))
                        <a
                            href="{{ $item['url'] }}"
                            class="theme-chrome-nav__link"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endif
                @endforeach
            </div>

            <details class="theme-chrome-nav__mobile">
                <summary class="theme-chrome-nav__mobile-toggle">
                    {{ __('capell-foundation-theme::generic.menu') }}
                </summary>
                <div class="theme-chrome-nav__mobile-panel">
                    @foreach ($section->items as $item)
                        @if (! empty($item['url']) && ! empty($item['label']))
                            <a
                                href="{{ $item['url'] }}"
                                class="theme-chrome-nav__link"
                            >
                                {{ $item['label'] }}
                            </a>
                        @endif
                    @endforeach
                </div>
            </details>
        @endif

        @if ($section->ctaLabel && $section->ctaUrl)
            <a
                href="{{ $section->ctaUrl }}"
                class="theme-chrome-nav__cta"
            >
                {{ $section->ctaLabel }}
            </a>
        @endif
    </div>
</nav>
<span
    id="main-content"
    tabindex="-1"
></span>
