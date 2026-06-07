@php
    $items = $section->items ?? $section->projects ?? [];
    $filters = $section->filters ?? collect($items)
        ->flatMap(static fn (array $item): array => array_filter([
            $item['discipline'] ?? null,
            $item['type'] ?? null,
        ]))
        ->unique()
        ->values()
        ->all();
@endphp

<section class="theme-project-showcase mx-auto max-w-7xl px-6 py-20">
    <div class="grid gap-6 lg:grid-cols-[0.7fr_1.3fr] lg:items-end">
        <div>
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.project_showcase') }}
            </p>
            <h2 class="mt-4 max-w-3xl text-5xl font-black tracking-tight">
                {{ $section->heading }}
            </h2>
        </div>

        @if ($section->summary)
            <p class="max-w-2xl text-zinc-400 lg:justify-self-end">
                {{ $section->summary }}
            </p>
        @endif
    </div>

    @if ($filters !== [])
        <div class="mt-8 flex flex-wrap gap-2">
            <span
                class="rounded-full border border-white/15 bg-white px-4 py-2 text-xs font-black tracking-[0.18em] text-zinc-950 uppercase"
            >
                {{ __('capell-theme-agency::generic.project_filter_all') }}
            </span>
            @foreach ($filters as $filter)
                <span
                    class="rounded-full border border-white/15 px-4 py-2 text-xs font-black tracking-[0.18em] text-zinc-300 uppercase"
                >
                    {{ $filter }}
                </span>
            @endforeach
        </div>
    @endif

    @if ($items !== [])
        <div class="mt-10 grid gap-5 lg:grid-cols-12">
            @foreach ($items as $item)
                <a
                    href="{{ $item['url'] ?? '#' }}"
                    class="{{ $loop->first ? 'lg:col-span-7 lg:row-span-2' : 'lg:col-span-5' }} group overflow-hidden rounded-[1.75rem] bg-white p-4 text-zinc-950 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl"
                >
                    @if (! empty($item['image']) || ! empty($item['imageUrl']))
                        <img
                            src="{{ $item['image'] ?? $item['imageUrl'] }}"
                            alt="{{ $item['imageAlt'] ?? '' }}"
                            width="960"
                            height="720"
                            loading="lazy"
                            decoding="async"
                            class="{{ $loop->first ? 'aspect-[16/11]' : 'aspect-[4/3]' }} w-full rounded-[1.25rem] object-cover transition duration-300 group-hover:scale-[1.025]"
                        />
                    @else
                        <div
                            class="{{ $loop->first ? 'aspect-[16/11]' : 'aspect-[4/3]' }} site-brand-gradient rounded-[1.25rem] p-4"
                            aria-hidden="true"
                        >
                            <div
                                class="flex h-full flex-col justify-between rounded-2xl bg-zinc-950/90 p-4"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="h-3 w-20 rounded-full bg-white"
                                    ></span>
                                    <span
                                        class="h-3 w-10 rounded-full bg-[var(--theme-accent)]"
                                    ></span>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <span
                                        class="h-14 rounded-xl bg-white/15"
                                    ></span>
                                    <span
                                        class="h-14 rounded-xl bg-white/25"
                                    ></span>
                                    <span
                                        class="h-14 rounded-xl bg-white/15"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="p-2 pt-5">
                        <div
                            class="flex flex-wrap items-center gap-2 text-xs font-black tracking-[0.18em] uppercase"
                        >
                            <span class="text-[var(--theme-primary)]">
                                {{ $item['discipline'] ?? $item['type'] ?? __('capell-theme-agency::generic.case_signal') }}
                            </span>
                            @if (! empty($item['year']))
                                <span class="text-zinc-400">/</span>
                                <span class="text-zinc-500">
                                    {{ __('capell-theme-agency::generic.project_year_label') }}
                                    {{ $item['year'] }}
                                </span>
                            @endif
                        </div>

                        <h3
                            class="mt-3 text-2xl font-black tracking-tight group-hover:text-[var(--theme-primary)]"
                        >
                            {{ $item['title'] }}
                        </h3>

                        @if (! empty($item['summary']))
                            <p class="mt-3 text-sm leading-6 text-zinc-600">
                                {{ $item['summary'] }}
                            </p>
                        @endif

                        <div
                            class="mt-5 flex flex-wrap items-center justify-between gap-3 text-xs font-black"
                        >
                            <span
                                class="rounded-full bg-zinc-950 px-3 py-1 text-white"
                            >
                                {{ $item['stage'] ?? __('capell-theme-agency::generic.case_study_signal') }}
                            </span>
                            <span class="text-zinc-400">
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @else
        <div
            class="mt-10 rounded-[1.5rem] border border-dashed border-white/20 p-8"
        >
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.project_empty_title') }}
            </p>
            <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-400">
                {{ __('capell-theme-agency::generic.project_empty_summary') }}
            </p>
        </div>
    @endif
</section>
