<section
    class="healthcare-proof border-y border-stone-200 bg-[#14323a] text-white"
>
    <div class="px-6">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#f59e0b] uppercase"
                >
                    {{ __('capell-theme-healthcare::generic.clinical_trust_label') }}
                </p>
                <h2
                    class="mt-4 max-w-xl text-4xl font-black tracking-tight text-white"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-md text-stone-300">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div
                class="theme-carousel relative"
                data-carousel="healthcare-proof"
            >
                <div
                    class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:overflow-visible md:pr-0 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach (($section->items ?? []) as $index => $item)
                        <article
                            class="{{ $loop->first ? 'md:col-span-2 md:grid md:grid-cols-[0.9fr_1.1fr]' : '' }} min-w-[270px] snap-start overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] md:min-w-0"
                        >
                            <div class="p-6">
                                <p
                                    class="text-xs font-black tracking-[0.18em] text-[#8de4db] uppercase"
                                >
                                    {{ $item['name'] ?? $item['logo'] ?? __('capell-theme-healthcare::generic.clinical_review') }}
                                </p>
                                <p
                                    class="mt-4 text-5xl leading-none font-black text-white"
                                >
                                    {{ $item['metric'] ?? $item['quote'] ?? '' }}
                                </p>
                                @if ($item['summary'] ?? null)
                                    <p
                                        class="mt-4 text-sm leading-6 text-stone-300"
                                    >
                                        {{ $item['summary'] }}
                                    </p>
                                @endif

                                @if ($item['role'] ?? null)
                                    <p class="mt-3 text-sm text-stone-400">
                                        {{ $item['role'] }}
                                    </p>
                                @endif

                                <div
                                    class="mt-5 flex flex-wrap gap-2 text-xs font-black"
                                >
                                    <span
                                        class="rounded-full bg-white px-3 py-1 text-[#0f766e]"
                                    >
                                        {{ __('capell-theme-healthcare::generic.safety_review_signal') }}
                                    </span>
                                    <span
                                        class="rounded-full bg-[#f59e0b] px-3 py-1 text-[#14323a]"
                                    >
                                        {{ __('capell-theme-healthcare::generic.outcome_signal') }}
                                    </span>
                                </div>
                            </div>

                            @if ($loop->first)
                                <div
                                    class="border-t border-white/10 bg-[#0e2730] p-6 md:border-t-0 md:border-l"
                                >
                                    <div
                                        class="flex items-center justify-between gap-4"
                                    >
                                        <p
                                            class="text-xs font-black text-[#8de4db] uppercase"
                                        >
                                            {{ __('capell-theme-healthcare::generic.care_pathway') }}
                                        </p>
                                        <span
                                            class="rounded-full bg-[#f59e0b] px-2 py-1 text-xs font-black text-[#14323a]"
                                        >
                                            {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>

                                    <div
                                        class="mt-6 grid grid-cols-[1fr_2rem_1fr] items-center gap-2"
                                        aria-hidden="true"
                                    >
                                        <span
                                            class="h-4 rounded-full bg-[#8de4db]"
                                        ></span>
                                        <span
                                            class="h-4 rounded-full bg-[#f59e0b]"
                                        ></span>
                                        <span
                                            class="h-4 rounded-full bg-white/20"
                                        ></span>
                                    </div>

                                    <div
                                        class="mt-4 grid grid-cols-3 gap-2"
                                        aria-hidden="true"
                                    >
                                        <span
                                            class="h-12 rounded-md bg-white/10"
                                        ></span>
                                        <span
                                            class="h-12 rounded-md bg-white/20"
                                        ></span>
                                        <span
                                            class="h-12 rounded-md bg-white/10"
                                        ></span>
                                    </div>

                                    <p
                                        class="mt-5 text-xs font-black text-[#f59e0b] uppercase"
                                    >
                                        {{ __('capell-theme-healthcare::generic.escalation_signal') }}
                                    </p>
                                </div>
                            @endif
                        </article>
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
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="healthcare-proof"]')
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
