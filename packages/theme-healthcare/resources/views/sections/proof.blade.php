<section
    class="healthcare-proof border-y border-stone-200 bg-[#14323a] text-white"
>
    <div class="px-6">
        <div class="mx-auto max-w-3xl text-center">
            <h2
                class="mx-auto max-w-3xl text-4xl font-black tracking-tight text-white"
            >
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mx-auto mt-4 max-w-2xl text-stone-300">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="healthcare-proof"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach (($section->items ?? []) as $item)
                    <figure
                        class="min-w-[270px] snap-start rounded-2xl border border-white/10 bg-white/[0.04] p-6"
                    >
                        <blockquote class="text-2xl font-black text-white">
                            {{ $item['metric'] ?? $item['quote'] ?? '' }}
                        </blockquote>
                        <figcaption
                            class="mt-4 text-sm font-bold text-[#f59e0b]"
                        >
                            {{ $item['name'] ?? $item['logo'] ?? '' }}
                        </figcaption>
                        @if ($item['role'] ?? null)
                            <p class="mt-1 text-sm text-stone-400">
                                {{ $item['role'] }}
                            </p>
                        @endif
                    </figure>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="Previous proof"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="Next proof"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="healthcare-proof"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(280, Math.floor(track.clientWidth * 0.82))

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
