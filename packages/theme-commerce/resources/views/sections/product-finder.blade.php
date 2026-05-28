@php
    $filters = $section->items ?? $section->filters ?? [];
@endphp

<section class="retail-finder bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr] lg:items-center">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="retail-frame bg-[#fffaf3] p-5">
            <div class="mt-1 grid gap-3">
                @foreach ($filters as $filter)
                    <div
                        class="rounded-xl border border-stone-200 bg-white p-4"
                    >
                        <p class="text-xs font-black text-[#1f5f4a] uppercase">
                            {{ $filter['group'] ?? __('capell-theme-commerce::generic.finder_filter') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach (($filter['options'] ?? [$filter['title'] ?? $filter['label'] ?? '']) as $option)
                                <span
                                    class="rounded-full border border-stone-200 px-3 py-1 text-sm font-bold text-[#17211c]"
                                >
                                    {{ $option }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <div
                class="theme-carousel relative mt-4"
                data-carousel="commerce-finder"
            >
                <div
                    class="mt-4 flex snap-x snap-mandatory [scrollbar-width:none] gap-3 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-4 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($filters as $tag)
                        <button
                            type="button"
                            class="min-w-[150px] snap-start rounded-full border border-[#d2dfd4] bg-[#fffaf3] px-4 py-2 text-xs font-black text-[#17211c]"
                        >
                            {{ $tag['group'] ?? $tag['title'] ?? $tag['label'] ?? 'Filter' }}
                        </button>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="Previous finder tags"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="Next finder tags"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="commerce-finder"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(180, Math.floor(track.clientWidth * 0.8))

            const updateButtons = () => {
                const canScroll = track.scrollWidth > track.clientWidth + 1
                prev.classList.toggle(
                    'hidden',
                    !canScroll || track.scrollLeft <= 2,
                )
                next.classList.toggle(
                    'hidden',
                    !canScroll ||
                        track.scrollLeft >=
                            track.scrollWidth - track.clientWidth - 2,
                )
            }

            prev.addEventListener('click', () =>
                track.scrollBy({ left: -step(), behavior: 'smooth' }),
            )
            next.addEventListener('click', () =>
                track.scrollBy({ left: step(), behavior: 'smooth' }),
            )
            track.addEventListener('scroll', updateButtons, { passive: true })
            window.addEventListener('resize', updateButtons)
            updateButtons()
        })
</script>
