@php
    $proofs = $section->items ?? [
        ['metric' => '98%', 'label' => 'Satisfaction', 'summary' => 'Satisfied service outcomes across local accounts.'],
        ['metric' => '24h', 'label' => 'Response', 'summary' => 'Median first-response time with escalation support.'],
        ['metric' => '34', 'label' => 'Coverage', 'summary' => 'Communities with active local service workflows.'],
    ];
@endphp

<section class="theme-section theme-section-proof bg-[#06120f] text-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#fb923c] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.proof_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-white">
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-300 md:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

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
                        class="min-w-[230px] snap-start border border-white/15 bg-white/8 p-5 transition hover:-translate-y-1 hover:border-[#fb923c] hover:bg-white/12"
                    >
                        <p
                            class="text-xs font-black tracking-[0.18em] text-[#99f6e4] uppercase"
                        >
                            {{ $proof['label'] ?? $proof['name'] ?? __('capell-theme-local-services::generic.proof_signal') }}
                        </p>
                        <p class="mt-3 text-4xl font-black text-white">
                            {{ $proof['metric'] }}
                        </p>
                        <p class="mt-3 text-sm leading-6 text-slate-300">
                            {{ $proof['summary'] }}
                        </p>
                        <div
                            class="mt-5 h-2 bg-[#f97316]"
                            aria-hidden="true"
                        ></div>
                    </article>
                @endforeach
            </div>
            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white p-2 text-sm font-semibold text-[#17211c] shadow-md"
                aria-label="Previous proof points"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white p-2 text-sm font-semibold text-[#17211c] shadow-md"
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
