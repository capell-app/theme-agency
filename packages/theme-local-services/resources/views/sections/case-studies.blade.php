@php
    $studies = $section->items ?? [
        ['title' => 'Community-first emergency response', 'summary' => 'Reduced downtime with one-day turnaround.', 'metric' => '2x'],
        ['title' => 'Property operations optimization', 'summary' => 'Standardized workflows across three service zones.', 'metric' => '36%'],
        ['title' => 'Local teams at scale', 'summary' => 'Built a shared operating rhythm across 34 accounts.', 'metric' => '34'],
    ];
@endphp

<section class="theme-section theme-section-case-studies">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <h2 class="text-3xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <p class="mt-4 max-w-2xl text-stone-600">
            {{ __('capell-theme-local-services::generic.case_studies_copy') }}
        </p>
        <div
            class="theme-carousel relative mt-8"
            data-carousel="local-services-cases"
        >
            <div
                class="flex gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-2 sm:overflow-x-visible sm:pr-0 sm:pb-0 lg:grid-cols-3"
                data-carousel-track
            >
                @foreach ($studies as $study)
                    <article
                        class="min-w-[260px] snap-start rounded-xl border border-stone-200 bg-white p-5 transition hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                    >
                        <p
                            class="text-xs font-black tracking-[0.2em] text-[#17211c]"
                        >
                            {{ $study['metric'] ?? 'Result' }}
                        </p>
                        <h3 class="mt-2 text-lg font-black">
                            {{ $study['title'] }}
                        </h3>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $study['summary'] ?? '' }}
                        </p>
                    </article>
                @endforeach
            </div>
            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Previous case studies"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
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
        .querySelectorAll('[data-carousel="local-services-cases"]')
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
