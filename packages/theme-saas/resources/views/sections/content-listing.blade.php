@if (in_array($section->variant ?? null, ['gallery', 'pathways', 'spotlight'], true))
    @include('capell-foundation-theme::theme.sections.content-listing', ['section' => $section])
@else
    <section class="saas-directory bg-white">
        <div class="px-6">
            <div class="grid gap-4 md:grid-cols-[0.75fr_1fr] md:items-end">
                <h2 class="text-4xl font-black tracking-tight text-slate-950">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="max-w-2xl text-lg md:justify-self-end">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div
                class="theme-carousel relative mt-10"
                data-carousel="saas-directory"
            >
                <div
                    class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:gap-6 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($section->items as $item)
                        <a
                            href="{{ $item['url'] ?? '#' }}"
                            class="group min-w-[240px] snap-start rounded-2xl border border-slate-200 bg-slate-50/70 p-6 transition hover:border-blue-300 hover:bg-white md:min-w-0"
                        >
                            @if ($item['type'] ?? null)
                                <p
                                    class="mb-4 text-xs font-black tracking-widest text-cyan-700 uppercase"
                                >
                                    {{ $item['type'] }}
                                </p>
                            @endif

                            <h3
                                class="text-xl font-black group-hover:text-blue-700"
                            >
                                {{ $item['title'] }}
                            </h3>
                            <p class="mt-3 text-sm">
                                {{ $item['summary'] ?? '' }}
                            </p>
                        </a>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                    aria-label="Previous directory card"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                    aria-label="Next directory card"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </section>

    <script>
        document
            .querySelectorAll('[data-carousel="saas-directory"]')
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

                track.addEventListener('scroll', updateButtons, {
                    passive: true,
                })
                window.addEventListener('resize', updateButtons)
                updateButtons()
            })
    </script>
@endif
