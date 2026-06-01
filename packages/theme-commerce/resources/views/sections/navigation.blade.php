<nav
    class="retail-navigation sticky top-0 z-20 border-b border-stone-200/80 bg-[#fffaf3]/95 backdrop-blur"
    aria-label="{{ __('capell-theme-commerce::generic.main_navigation') }}"
>
    <div class="flex items-center justify-between px-6 py-4">
        <a
            href="/"
            class="text-base font-black text-[#17211c]"
        >
            {{ $section->brandName }}
        </a>

        <div
            class="hidden items-center gap-7 text-sm font-bold text-stone-600 md:flex"
        >
            @foreach (($section->items ?? []) as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="transition hover:text-[var(--retail-primary)]"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <div class="flex items-center gap-3">
            @if (($section->ctaLabel ?? null) && ($section->ctaUrl ?? null))
                <a
                    href="{{ $section->ctaUrl }}"
                    class="retail-cta retail-cta-primary"
                >
                    {{ $section->ctaLabel }}
                </a>
            @endif

            <details class="relative md:hidden">
                <summary
                    class="cursor-pointer list-none rounded-full border border-stone-300 px-3 py-2 text-sm font-bold text-stone-700 marker:hidden"
                >
                    {{ __('capell-theme-commerce::generic.menu') }}
                </summary>
                <div
                    class="absolute right-0 z-30 mt-3 grid min-w-52 gap-3 rounded-xl border border-stone-200 bg-white p-4 text-sm font-bold text-stone-600 shadow-xl"
                >
                    @foreach (($section->items ?? []) as $item)
                        <a
                            href="{{ $item['url'] }}"
                            class="hover:text-[var(--retail-primary)]"
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
