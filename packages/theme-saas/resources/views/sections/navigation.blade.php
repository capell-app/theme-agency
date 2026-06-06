<nav
    class="saas-navigation sticky top-0 z-20 border-b border-slate-200/80 bg-white/92 backdrop-blur"
    aria-label="{{ __('capell-theme-saas::generic.main_navigation') }}"
>
    <div class="flex items-center justify-between px-6 py-4">
        <a
            href="/"
            class="text-base font-black text-slate-950"
        >
            {{ $section->brandName }}
        </a>

        <div
            class="saas-navigation__links hidden items-center gap-7 text-sm font-bold text-slate-600 md:flex"
        >
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="transition hover:text-[var(--saas-primary)]"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            @if ($section->ctaLabel && $section->ctaUrl)
                <a
                    href="{{ $section->ctaUrl }}"
                    class="saas-cta saas-cta-primary"
                >
                    {{ $section->ctaLabel }}
                </a>
            @endif

            <details class="relative md:hidden">
                <summary
                    class="cursor-pointer list-none rounded-full border border-slate-300 px-3 py-2 text-sm font-bold text-slate-700 marker:hidden"
                >
                    {{ __('capell-theme-saas::generic.menu') }}
                </summary>
                <div
                    class="absolute right-0 z-30 mt-3 grid min-w-52 gap-3 rounded-xl border border-slate-200 bg-white p-4 text-sm font-bold text-slate-600 shadow-xl"
                >
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="hover:text-[var(--saas-primary)]"
                        >
                            {{ $item['label'] }}
                        </a>
                    @endforeach
                </div>
            </details>
        </div>
    </div>
</nav>
<span
    id="main-content"
    tabindex="-1"
></span>
