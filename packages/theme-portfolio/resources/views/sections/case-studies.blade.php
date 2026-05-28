<section
    id="case-studies"
    class="theme-section theme-section-case-studies bg-gradient-to-br from-slate-950 to-slate-900 px-6 py-20 text-white"
>
    <div class="mx-auto flex max-w-5xl flex-col gap-6">
        @isset($heading)
            <h2 class="max-w-3xl text-4xl font-black tracking-tight">
                {{ $heading }}
            </h2>
        @endisset

        <p class="max-w-3xl text-lg text-slate-200">
            {{ $contentSectionsAvailable ?? false ? __('capell-theme-portfolio::generic.case_studies_connected') : __('capell-theme-portfolio::generic.case_studies_static') }}
        </p>

        <div
            class="theme-carousel relative mt-6"
            data-carousel="portfolio-case-studies"
        >
            <div
                class="mx-auto flex max-w-5xl snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach (($section->items ?? []) as $item)
                    <article
                        class="min-w-[280px] snap-start rounded-2xl border border-white/15 bg-white/[0.08] p-4"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-[#f8fafc]"
                        >
                            {{ $item['type'] ?? __('capell-theme-portfolio::generic.featured_heading') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-white">
                            {{ $item['title'] ?? $item['name'] ?? '' }}
                        </h3>
                        <p class="mt-3 text-sm text-slate-200">
                            {{ $item['summary'] ?? $item['description'] ?? '' }}
                        </p>
                    </article>
                @endforeach

                @if (empty($section->items))
                    <article
                        class="min-w-[280px] snap-start rounded-2xl border border-white/15 bg-white/[0.08] p-4"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-[#f8fafc]"
                        >
                            {{ __('capell-theme-portfolio::generic.featured_heading') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-white">
                            Premium portfolio redesign
                        </h3>
                        <p class="mt-3 text-sm text-slate-200">
                            Brand-forward campaign work built for conversion and
                            credibility.
                        </p>
                    </article>
                    <article
                        class="min-w-[280px] snap-start rounded-2xl border border-white/15 bg-white/[0.08] p-4"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-[#f8fafc]"
                        >
                            {{ __('capell-theme-portfolio::generic.portfolio_copy') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-white">
                            Product storytelling platform
                        </h3>
                        <p class="mt-3 text-sm text-slate-200">
                            Structured narratives and media-led landing paths
                            for high-intent traffic.
                        </p>
                    </article>
                    <article
                        class="min-w-[280px] snap-start rounded-2xl border border-white/15 bg-white/[0.08] p-4"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-[#f8fafc]"
                        >
                            {{ __('capell-theme-portfolio::generic.project_heading') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-white">
                            Editorial design system
                        </h3>
                        <p class="mt-3 text-sm text-slate-200">
                            Content-first experiences built to keep visitors
                            reading and converting.
                        </p>
                    </article>
                @endif
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-white/25 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="Previous case studies"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-white/25 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                aria-label="Next case studies"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="portfolio-case-studies"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(280, Math.floor(track.clientWidth * 0.8))

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
