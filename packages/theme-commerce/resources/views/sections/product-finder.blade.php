@php
    $filters = $section->items ?? $section->filters ?? [];
@endphp

<section class="retail-finder bg-white">
    <div
        class="grid min-w-0 gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center"
    >
        <div class="min-w-0">
            <h2
                class="text-4xl font-black tracking-tight text-[var(--retail-ink)]"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="retail-frame max-w-full min-w-0 overflow-hidden bg-[var(--retail-surface)] p-5"
        >
            <div class="mt-1 grid gap-3">
                @foreach ($filters as $filter)
                    <div
                        class="rounded-xl border border-stone-200 bg-white p-4"
                    >
                        <p
                            class="text-xs font-black text-[var(--retail-primary)] uppercase"
                        >
                            {{ $filter['group'] ?? __('capell-theme-commerce::generic.finder_filter') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (($filter['options'] ?? [$filter['title'] ?? $filter['label'] ?? '']) as $option)
                                <span
                                    class="rounded-full border border-stone-200 px-3 py-1 text-sm font-bold text-[var(--retail-ink)]"
                                >
                                    {{ $option }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="theme-carousel relative mt-4 max-w-full min-w-0 overflow-hidden"
                data-carousel="commerce-finder"
            >
                <div
                    class="mt-4 flex max-w-full snap-x snap-mandatory [scrollbar-width:none] gap-3 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-4 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($filters as $tag)
                        <button
                            type="button"
                            class="min-w-[150px] snap-start rounded-full border border-[var(--retail-line)] bg-[var(--retail-surface)] px-4 py-2 text-xs font-black text-[var(--retail-ink)]"
                        >
                            {{ $tag['group'] ?? $tag['title'] ?? $tag['label'] ?? 'Filter' }}
                        </button>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="{{ __('capell-theme-commerce::generic.carousel_previous') }}"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="{{ __('capell-theme-commerce::generic.carousel_next') }}"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </div>
</section>
