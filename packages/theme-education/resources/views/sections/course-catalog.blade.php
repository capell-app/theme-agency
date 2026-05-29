<section class="theme-section theme-section-course-catalog">
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <h2 class="text-4xl font-black tracking-tight text-[#1d4ed8]">
                {{ $heading }}
            </h2>
        </div>
    @endisset

    <div
        class="theme-carousel relative mx-auto mt-6 max-w-5xl px-6 pb-14"
        data-carousel="education-course-catalog"
    >
        <div
            class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
            data-carousel-track
        >
            <article
                class="min-w-[250px] snap-start rounded-xl border border-indigo-200 bg-white p-4"
            >
                <p class="text-xs font-black tracking-widest text-[#1d4ed8]">
                    Online
                </p>
                <h3 class="mt-2 text-lg font-black">Starter Path</h3>
                <p class="mt-2 text-sm text-stone-600">
                    Quick onboarding and fundamentals to speed your team.
                </p>
            </article>
            <article
                class="min-w-[250px] snap-start rounded-xl border border-indigo-200 bg-white p-4"
            >
                <p class="text-xs font-black tracking-widest text-[#1d4ed8]">
                    Cohorts
                </p>
                <h3 class="mt-2 text-lg font-black">Cohort Tracks</h3>
                <p class="mt-2 text-sm text-stone-600">
                    Guided sessions with checkpoints and mentor review.
                </p>
            </article>
            <article
                class="min-w-[250px] snap-start rounded-xl border border-indigo-200 bg-white p-4"
            >
                <p class="text-xs font-black tracking-widest text-[#1d4ed8]">
                    Certification
                </p>
                <h3 class="mt-2 text-lg font-black">Advanced Badge</h3>
                <p class="mt-2 text-sm text-stone-600">
                    Practical assessments with publish-ready outcomes.
                </p>
            </article>
        </div>

        <button
            type="button"
            class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-indigo-200 bg-white p-2 text-sm font-semibold shadow-md"
            aria-label="Previous catalog cards"
            data-carousel-prev
        >
            ‹
        </button>
        <button
            type="button"
            class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-indigo-200 bg-white p-2 text-sm font-semibold shadow-md"
            aria-label="Next catalog cards"
            data-carousel-next
        >
            ›
        </button>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="education-course-catalog"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(250, Math.floor(track.clientWidth * 0.85))

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
