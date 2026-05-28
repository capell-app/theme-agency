<section class="retail-proof border-y border-stone-200 bg-[#17211c] text-white">
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
            data-carousel="commerce-proof"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach (($section->items ?? []) as $item)
                    <figure
                        class="group min-w-[260px] snap-start rounded-2xl border border-white/10 bg-white/[0.04] p-6 transition hover:border-[#e86f5c]/60 hover:bg-white/[0.08] sm:min-w-[320px]"
                    >
                        <blockquote
                            class="text-2xl leading-tight font-black text-white"
                        >
                            {{ $item['metric'] ?? $item['quote'] ?? '' }}
                        </blockquote>
                        <p class="mt-2 text-sm text-stone-200">
                            {{ $item['excerpt'] ?? $item['summary'] ?? '' }}
                        </p>
                        <figcaption
                            class="mt-4 text-sm font-black tracking-wide text-[#e86f5c] uppercase"
                        >
                            {{ $item['name'] ?? $item['logo'] ?? '' }}
                        </figcaption>
                        @if ($item['role'] ?? null)
                            <p class="mt-1 text-xs text-stone-400">
                                {{ $item['role'] }}
                            </p>
                        @endif
                    </figure>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white/20 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="Previous proofs"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white/20 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="Next proofs"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="commerce-proof"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(320, Math.floor(track.clientWidth * 0.9))

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
