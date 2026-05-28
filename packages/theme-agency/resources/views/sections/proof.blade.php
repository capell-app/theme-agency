<section class="theme-proof mx-auto max-w-7xl px-6 py-20">
    <div class="rounded-[2rem] bg-white p-8 shadow-sm">
        <h2 class="text-4xl font-black tracking-tight text-zinc-950">
            {{ $section->heading }}
        </h2>
        @if ($section->summary)
            <p class="mt-3 max-w-2xl text-zinc-600">{{ $section->summary }}</p>
        @endif

        <div class="theme-carousel relative mt-8" data-carousel="agency-proof">
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($section->items as $item)
                    <figure
                        class="min-w-[240px] snap-start rounded-2xl bg-zinc-950 p-6 text-white md:min-w-0"
                    >
                        @if (! empty($item['image']))
                            <img
                                src="{{ $item['image'] }}"
                                alt="{{ $item['name'] ?? $item['logo'] ?? '' }}"
                                class="mb-4 aspect-[16/10] w-full rounded-lg object-cover opacity-90"
                            />
                        @endif

                        <blockquote class="text-lg leading-7 font-semibold">
                            {{ $item['quote'] ?? $item['metric'] ?? '' }}
                        </blockquote>
                        <figcaption
                            class="mt-5 text-sm text-[var(--theme-accent)]"
                        >
                            {{ $item['name'] ?? $item['logo'] ?? '' }}
                        </figcaption>
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
        .querySelectorAll('[data-carousel="agency-proof"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(260, Math.floor(track.clientWidth * 0.82))

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

            prev.addEventListener('click', () => {
                track.scrollBy({ left: -step(), behavior: 'smooth' })
            })
            next.addEventListener('click', () => {
                track.scrollBy({ left: step(), behavior: 'smooth' })
            })
            track.addEventListener('scroll', updateButtons, { passive: true })
            window.addEventListener('resize', updateButtons)
            updateButtons()
        })
</script>
