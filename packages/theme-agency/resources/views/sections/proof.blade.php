<section class="theme-proof mx-auto max-w-7xl px-6 py-20">
    <div class="rounded-[2rem] bg-white p-8 text-zinc-950 shadow-sm">
        <div class="grid gap-5 lg:grid-cols-[0.68fr_1.32fr] lg:items-end">
            <div>
                <p
                    class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
                >
                    {{ __('capell-theme-agency::generic.proof_wall') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
            </div>
            @if ($section->summary)
                <p class="max-w-2xl text-zinc-600 lg:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="theme-carousel relative mt-8" data-carousel="agency-proof">
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($section->items as $item)
                    <figure
                        class="min-w-[250px] snap-start rounded-2xl bg-zinc-950 p-5 text-white md:min-w-0"
                    >
                        @if (! empty($item['image']))
                            <img
                                src="{{ $item['image'] }}"
                                alt="{{ $item['name'] ?? $item['logo'] ?? '' }}"
                                class="mb-4 aspect-[16/10] w-full rounded-lg object-cover opacity-90"
                            />
                        @endif

                        <p class="font-mono text-4xl font-black">
                            {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                        </p>
                        <blockquote
                            class="mt-4 text-sm leading-6 text-zinc-200"
                        >
                            {{ $item['quote'] ?? $item['summary'] ?? '' }}
                        </blockquote>
                        <figcaption
                            class="mt-5 border-t border-white/10 pt-4 text-sm font-black text-[var(--theme-accent)]"
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
