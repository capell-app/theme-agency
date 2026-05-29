<section class="saas-features bg-white">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2 class="text-4xl font-black tracking-tight text-slate-950">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-4 max-w-2xl text-lg">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="saas-features"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:gap-6 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($section->features as $feature)
                    <article
                        class="group min-w-[260px] snap-start rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-1 hover:border-blue-300 hover:shadow-xl"
                    >
                        <div
                            class="mb-5 rounded-xl border border-slate-800 bg-slate-950 p-4 text-white"
                        >
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="rounded-full bg-cyan-300 px-2.5 py-1 text-[0.65rem] font-black tracking-widest text-slate-950 uppercase"
                                >
                                    {{ $feature['type'] ?? $feature['icon'] ?? __('capell-theme-saas::generic.product_signal') }}
                                </span>
                                <span
                                    class="rounded-full bg-blue-500 px-2.5 py-1 text-[0.65rem] font-black tracking-widest text-white uppercase"
                                >
                                    {{ __('capell-theme-saas::generic.experiment_label') }}
                                </span>
                            </div>

                            <div
                                class="mt-6 rounded-lg border border-white/10 bg-white/[0.04] p-3"
                            >
                                <p
                                    class="text-xs font-black text-cyan-200 uppercase"
                                >
                                    {{ __('capell-theme-saas::generic.activation_flow_label') }}
                                </p>
                                <div
                                    class="mt-4 grid grid-cols-3 gap-2 text-center text-[0.65rem] font-black uppercase"
                                >
                                    <span
                                        class="rounded-md bg-cyan-300 px-2 py-2 text-slate-950"
                                    >
                                        {{ __('capell-theme-saas::generic.trial_step_label') }}
                                    </span>
                                    <span
                                        class="rounded-md bg-blue-500 px-2 py-2 text-white"
                                    >
                                        {{ __('capell-theme-saas::generic.aha_step_label') }}
                                    </span>
                                    <span
                                        class="rounded-md bg-white/10 px-2 py-2 text-slate-200"
                                    >
                                        {{ __('capell-theme-saas::generic.expansion_step_label') }}
                                    </span>
                                </div>
                                <div
                                    class="mt-4 h-1.5 rounded-full bg-white/10"
                                >
                                    <span
                                        class="block h-1.5 w-2/3 rounded-full bg-cyan-300"
                                    ></span>
                                </div>
                            </div>

                            <div
                                class="mt-3 grid grid-cols-2 gap-2 text-[0.65rem] font-black text-slate-200 uppercase"
                            >
                                <span class="rounded-md bg-white/10 px-2 py-2">
                                    {{ __('capell-theme-saas::generic.event_label') }}
                                </span>
                                <span class="rounded-md bg-white/10 px-2 py-2">
                                    {{ __('capell-theme-saas::generic.rule_label') }}
                                </span>
                            </div>
                        </div>
                        <h3 class="text-xl font-black">
                            {{ $feature['title'] }}
                        </h3>
                        <p class="mt-3 text-sm">
                            {{ $feature['description'] }}
                        </p>
                        <p
                            class="mt-5 text-xs font-black tracking-widest text-cyan-700 uppercase"
                        >
                            {{ $feature['metric'] ?? __('capell-theme-saas::generic.workflow_signal') }}
                        </p>
                    </article>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                aria-label="Previous feature card"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                aria-label="Next feature card"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="saas-features"]')
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
