<nav
    class="theme-navigation bg-zinc-950 text-white"
    aria-label="{{ __('capell-theme-agency::generic.main_navigation') }}"
>
    <div class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
        <a
            href="/"
            class="text-2xl font-black tracking-tight"
        >
            {{ $section->brandName }}
        </a>
        <div
            class="hidden items-center gap-6 text-sm font-semibold tracking-wide text-white/70 uppercase md:flex"
        >
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="hover:text-[var(--theme-accent)]"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
        <details class="relative md:hidden">
            <summary
                class="cursor-pointer list-none rounded-full border border-white/20 px-4 py-2 text-sm font-bold text-white marker:hidden"
            >
                {{ __('capell-theme-agency::generic.menu') }}
            </summary>
            <div
                class="absolute right-0 z-30 mt-3 grid min-w-48 gap-3 rounded-lg bg-zinc-900 p-4 text-sm font-semibold tracking-wide text-white/80 uppercase shadow-xl"
            >
                @foreach ($section->items as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="hover:text-[var(--theme-accent)]"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </details>
        @if ($section->ctaLabel && $section->ctaUrl)
            <a
                href="{{ $section->ctaUrl }}"
                class="rounded-full bg-[var(--theme-primary)] px-5 py-2 text-sm font-bold text-white"
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
