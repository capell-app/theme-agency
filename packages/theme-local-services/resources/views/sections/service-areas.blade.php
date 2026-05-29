<section class="theme-section theme-section-service-areas">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mx-auto mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-local-services::generic.service_areas_copy') }}
        </p>

        <div
            class="theme-carousel relative mt-8"
            data-carousel="local-services-areas"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach (['Downtown', 'Uptown', 'Westside', 'East Quarter', 'Airport District'] as $district)
                    <a
                        href="#"
                        class="min-w-[220px] snap-start rounded-2xl border border-stone-200 bg-[#f8fafc] px-5 py-4 text-sm font-bold text-[#17211c] transition hover:border-[#17211c] hover:bg-[#17211c] hover:text-white"
                    >
                        {{ $district }}
                    </a>
                @endforeach
            </div>
            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Previous service areas"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Next service areas"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>

    <script>
        document
            .querySelectorAll('[data-carousel="local-services-areas"]')
            .forEach((carousel) => {
                const track = carousel.querySelector('[data-carousel-track]')
                const prev = carousel.querySelector('[data-carousel-prev]')
                const next = carousel.querySelector('[data-carousel-next]')

                if (!track || !prev || !next) {
                    return
                }

                const step = () =>
                    Math.max(240, Math.floor(track.clientWidth * 0.8))

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

                track.addEventListener('scroll', updateButtons, {
                    passive: true,
                })
                window.addEventListener('resize', updateButtons)
                updateButtons()
            })
    </script>
</section>
