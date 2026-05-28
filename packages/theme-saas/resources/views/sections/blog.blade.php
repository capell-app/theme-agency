@php
    $blogAvailable ??= false;
    $articles = $section->items ?? [];
@endphp

<section class="saas-insights bg-slate-50">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2 class="text-4xl font-black tracking-tight text-slate-950">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-2xl text-lg">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="saas-insights"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:gap-6 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($articles as $article)
                    <a
                        href="{{ $blogAvailable ? ($article['url'] ?? '#') : '#' }}"
                        class="saas-insight-card min-w-[260px] snap-start rounded-2xl border border-slate-200 bg-white p-6 transition hover:border-blue-300"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-cyan-700 uppercase"
                        >
                            {{ $blogAvailable ? ($article['type'] ?? __('capell-theme-saas::generic.insight')) : __('capell-theme-saas::generic.resource') }}
                        </p>
                        <h3 class="mt-4 text-xl font-black">
                            {{ $article['title'] }}
                        </h3>
                        <p class="mt-3 text-sm">
                            {{ $article['summary'] ?? '' }}
                        </p>
                    </a>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                aria-label="Previous insight"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-slate-200 bg-white p-2 text-sm font-semibold text-slate-700 shadow-md"
                aria-label="Next insight"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="saas-insights"]')
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
