@php
    $navigation = $profile['navigation'];
    $brandInitials = collect(explode(' ', trim($section->brandName)))
        ->filter()
        ->map(fn (string $word): string => mb_substr($word, 0, 1))
        ->take(2)
        ->implode('') ?: 'C';
@endphp

<nav
    class="bg-white/92 border-b border-black/10 backdrop-blur"
    aria-label="{{ __('capell-theme-business-solutions::generic.main_navigation') }}"
>
    @if ($navigation === 'utility')
        <div
            class="bg-[var(--theme-primary)] px-5 py-2 text-sm font-semibold text-white"
        >
            <div
                class="business-theme-container flex flex-wrap items-center justify-between gap-2"
            >
                <span>{{ $profile['industry'] }}</span>
                <span>
                    {{ __('capell-theme-business-solutions::generic.quick_action') }}
                </span>
            </div>
        </div>
    @endif

    <div
        class="business-theme-container flex flex-wrap items-center justify-between gap-4 px-5 py-4"
    >
        <a href="/" class="flex items-center gap-3 font-bold">
            <span
                class="grid h-9 w-9 place-items-center rounded-[var(--theme-radius-value)] bg-[var(--theme-primary)] text-sm text-white"
            >
                {{ $brandInitials }}
            </span>
            <span>{{ $section->brandName }}</span>
        </a>

        <div class="hidden items-center gap-6 text-sm font-semibold md:flex">
            @foreach ($section->items as $item)
                <a
                    href="{{ $item['url'] }}"
                    class="text-slate-600 transition hover:text-[var(--theme-primary)]"
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </div>

        <details class="relative md:hidden">
            <summary
                class="cursor-pointer list-none rounded-[var(--theme-radius-value)] border border-black/15 px-3 py-2 text-sm font-semibold marker:hidden"
            >
                {{ __('capell-theme-business-solutions::generic.menu') }}
            </summary>
            <div
                class="absolute right-0 z-30 mt-3 grid min-w-52 gap-3 rounded-[var(--theme-radius-value)] border border-black/10 bg-white p-4 text-sm font-semibold shadow-xl"
            >
                @foreach ($section->items as $item)
                    <a
                        href="{{ $item['url'] }}"
                        class="hover:text-[var(--theme-primary)]"
                    >
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </details>

        @if ($navigation === 'toggle')
            <div
                class="hidden rounded-full border border-black/10 bg-slate-50 p-1 text-xs font-bold md:flex"
            >
                <span
                    class="rounded-full bg-[var(--theme-primary)] px-3 py-1.5 text-white"
                >
                    {{ __('capell-theme-business-solutions::generic.candidates') }}
                </span>
                <span class="px-3 py-1.5 text-slate-600">
                    {{ __('capell-theme-business-solutions::generic.employers') }}
                </span>
            </div>
        @endif

        @if ($section->ctaLabel && $section->ctaUrl)
            <a href="{{ $section->ctaUrl }}" class="business-theme-button">
                {{ $section->ctaLabel }}
            </a>
        @endif
    </div>
</nav>
<span id="main-content" tabindex="-1"></span>
