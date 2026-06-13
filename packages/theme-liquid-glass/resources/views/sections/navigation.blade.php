<nav
    class="theme-navigation liquid-glass-nav"
    aria-label="{{ __('capell-theme-liquid-glass::generic.main_navigation') }}"
>
    <div
        class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-5 sm:px-6 lg:px-8"
    >
        <a
            href="/"
            class="liquid-glass-brand"
        >
            {{ $section->brandName }}
        </a>

        <div class="hidden items-center gap-7 text-sm font-semibold md:flex">
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="liquid-glass-nav-link"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            @if ($section->ctaLabel && $section->ctaUrl)
                <a
                    href="{{ $section->ctaUrl }}"
                    class="liquid-glass-button hidden sm:inline-flex"
                >
                    {{ $section->ctaLabel }}
                </a>
            @endif

            <details class="relative md:hidden">
                <summary class="liquid-glass-menu-trigger">
                    {{ __('capell-theme-liquid-glass::generic.menu') }}
                </summary>
                <div class="liquid-glass-menu">
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="liquid-glass-nav-link"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach

                    @if ($section->ctaLabel && $section->ctaUrl)
                        <a
                            href="{{ $section->ctaUrl }}"
                            class="liquid-glass-button"
                        >
                            {{ $section->ctaLabel }}
                        </a>
                    @endif
                </div>
            </details>
        </div>
    </div>
</nav>

<span
    id="main-content"
    tabindex="-1"
></span>
