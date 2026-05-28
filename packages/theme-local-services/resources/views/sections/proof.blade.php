@php
    $proofs = $section->items ?? [
        ['metric' => '98%', 'label' => 'Satisfaction', 'summary' => 'Satisfied service outcomes across local accounts.'],
        ['metric' => '24h', 'label' => 'Response', 'summary' => 'Median first-response time with escalation support.'],
        ['metric' => '34', 'label' => 'Coverage', 'summary' => 'Communities with active local service workflows.'],
    ];
@endphp

<section class="theme-section theme-section-proof">
    <div class="mx-auto max-w-5xl px-6 py-14">
        @isset($heading)
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $heading }}
            </h2>
        @endisset

        <div
            class="theme-carousel relative mt-8"
            data-carousel="local-services-proof"
        >
            <div
                class="flex gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-3 sm:overflow-x-visible sm:pr-0 sm:pb-0"
                data-carousel-track
            >
                @foreach ($proofs as $proof)
                    <article
                        class="min-w-[220px] snap-start rounded-xl border border-stone-200 bg-white p-5 transition hover:-translate-y-1 hover:border-[#17211c] hover:bg-stone-950 hover:text-white"
                    >
                        <p
                            class="text-xs font-black tracking-[0.18em] text-slate-500"
                        >
                            {{ $proof['label'] }}
                        </p>
                        <p class="mt-3 text-3xl font-black text-[#17211c]">
                            {{ $proof['metric'] }}
                        </p>
                        <p class="mt-2 text-sm text-stone-600">
                            {{ $proof['summary'] }}
                        </p>
                    </article>
                @endforeach
            </div>
            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Previous proof points"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Next proof points"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="local-services-proof"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(240, Math.floor(track.clientWidth * 0.82))

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
