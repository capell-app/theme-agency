<section
    id="work-grid"
    class="theme-section theme-section-work-grid bg-[#f8fafc]"
>
    @isset($heading)
        <div class="mx-auto max-w-5xl px-6 py-14">
            <div class="grid gap-3 lg:grid-cols-[1fr_1.35fr] lg:items-end">
                <div>
                    <h2
                        class="text-4xl font-black tracking-tight text-[#0f172a]"
                    >
                        {{ $heading }}
                    </h2>
                    <p class="mt-4 max-w-2xl text-lg text-stone-600">
                        {{ __('capell-theme-portfolio::generic.work_grid_copy') ?? 'Selected portfolio projects with premium composition and clear visual hierarchy.' }}
                    </p>
                </div>
                <div class="grid grid-cols-3 gap-2 text-sm">
                    <p
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-center text-xs font-black tracking-[0.14em] text-slate-500"
                    >
                        30+ Projects
                    </p>
                    <p
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-center text-xs font-black tracking-[0.14em] text-slate-500"
                    >
                        12+ Industries
                    </p>
                    <p
                        class="rounded-full border border-slate-200 bg-white px-4 py-2 text-center text-xs font-black tracking-[0.14em] text-slate-500"
                    >
                        97% Retention
                    </p>
                </div>
            </div>
        </div>
    @endisset

    <div
        class="theme-carousel relative mt-4"
        data-carousel="portfolio-work-grid"
    >
        <div
            class="mx-auto mt-2 flex max-w-5xl [scrollbar-width:none] gap-4 overflow-x-auto px-6 pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
            data-carousel-track
        >
            @foreach (($section->items ?? []) as $project)
                <article
                    class="min-w-[250px] snap-start rounded-xl border border-slate-200 bg-white p-4"
                >
                    <p
                        class="text-xs font-black tracking-widest text-slate-500 uppercase"
                    >
                        {{ $project['type'] ?? __('capell-theme-portfolio::generic.projects_heading') }}
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#0f172a]">
                        {{ $project['title'] ?? $project['name'] ?? '' }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ $project['summary'] ?? $project['description'] ?? '' }}
                    </p>
                </article>
            @endforeach

            @if (empty($section->items))
                <article
                    class="min-w-[250px] snap-start rounded-xl border border-slate-200 bg-white p-4"
                >
                    <p
                        class="text-xs font-black tracking-widest text-slate-500 uppercase"
                    >
                        Visual Projects
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#0f172a]">
                        Landing suite
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Editorial and campaign modules packaged for growth.
                    </p>
                </article>
                <article
                    class="min-w-[250px] snap-start rounded-xl border border-slate-200 bg-white p-4"
                >
                    <p
                        class="text-xs font-black tracking-widest text-slate-500 uppercase"
                    >
                        Brand Systems
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#0f172a]">
                        Portfolio refresh
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        Premium visual system with section-level storytelling.
                    </p>
                </article>
                <article
                    class="min-w-[250px] rounded-xl border border-slate-200 bg-white p-4"
                >
                    <p
                        class="text-xs font-black tracking-widest text-slate-500 uppercase"
                    >
                        Conversion
                    </p>
                    <h3 class="mt-2 text-lg font-black text-[#0f172a]">
                        Case study platform
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        High-performance cards with clear next-step actions.
                    </p>
                </article>
            @endif
        </div>

        <button
            type="button"
            class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold shadow-md"
            aria-label="Previous projects"
            data-carousel-prev
        >
            ‹
        </button>
        <button
            type="button"
            class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold shadow-md"
            aria-label="Next projects"
            data-carousel-next
        >
            ›
        </button>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="portfolio-work-grid"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(250, Math.floor(track.clientWidth * 0.8))

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
