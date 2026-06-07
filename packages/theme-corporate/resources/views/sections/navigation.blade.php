<nav
    class="theme-navigation corporate-surface sticky top-0 z-40 border-b border-slate-200/80 backdrop-blur dark:border-white/10"
    aria-label="{{ __('capell-theme-corporate::generic.main_navigation') }}"
>
    <div
        class="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-5 gap-y-3 px-4 py-3 sm:px-6 sm:py-4"
    >
        <a
            href="/"
            class="group inline-flex items-center gap-2.5 text-sm font-semibold tracking-wide text-slate-950 dark:text-white"
        >
            <span
                class="inline-flex h-8 w-8 items-center justify-center rounded-[0.35rem] bg-slate-950 text-[0.7rem] font-bold text-white uppercase transition group-hover:bg-[var(--theme-primary)] dark:bg-white dark:text-slate-950"
            >
                {{ collect(explode(' ', trim($section->brandName)))->filter()->map(fn (string $word): string => mb_substr($word, 0, 1))->take(2)->implode('') ?: 'C' }}
            </span>
            <span>{{ $section->brandName }}</span>
        </a>
        <div
            class="order-3 hidden w-full items-center gap-5 overflow-x-auto text-sm text-slate-600 sm:w-auto md:order-none md:flex md:gap-6 dark:text-slate-300"
        >
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="transition hover:text-slate-950 dark:hover:text-white"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>
        <details
            class="relative order-3 md:hidden"
            data-corporate-menu
        >
            <summary
                class="cursor-pointer list-none rounded-[var(--corporate-card-radius)] border border-slate-300 px-3 py-2 text-sm font-medium text-slate-800 marker:hidden dark:border-white/15 dark:text-slate-200"
                aria-expanded="false"
            >
                {{ __('capell-theme-corporate::generic.menu') }}
            </summary>
            <div
                class="corporate-card-elevated absolute right-0 z-30 mt-3 grid min-w-48 gap-3 p-4 text-sm text-slate-600 dark:text-slate-300"
            >
                @foreach ($section->items as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="transition hover:text-slate-950 dark:hover:text-white"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </details>
        @if ($section->ctaLabel && $section->ctaUrl)
            <a
                href="{{ $section->ctaUrl }}"
                class="rounded-full border border-slate-300 px-3.5 py-1.5 text-sm font-medium text-slate-800 transition hover:border-slate-950 hover:text-slate-950 sm:px-4 sm:py-2 dark:border-white/15 dark:text-slate-200 dark:hover:border-white dark:hover:text-white"
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
