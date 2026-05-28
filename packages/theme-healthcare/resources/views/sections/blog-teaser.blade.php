@php
    $blogAvailable ??= false;
    $articles = $section->items ?? [];
@endphp

@if (in_array($section->variant ?? null, ['gallery', 'pathways', 'spotlight'], true))
    @include('capell-foundation-theme::theme.sections.content-listing', ['section' => $section])
@else
    <section class="healthcare-resources bg-[#f6fbfd]">
        <div class="px-6">
            <div
                class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
            >
                <div>
                    <h2
                        class="text-4xl font-black tracking-tight text-[#14323a]"
                    >
                        {{ $section->heading }}
                    </h2>
                    @if ($section->summary ?? null)
                        <p class="mt-4 max-w-2xl text-lg text-stone-600">
                            {{ $section->summary }}
                        </p>
                    @endif
                </div>
            </div>

            <div
                class="theme-carousel relative mt-10"
                data-carousel="healthcare-blog-teaser"
            >
                <div
                    class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($articles as $article)
                        @if ($blogAvailable)
                            <a
                                href="{{ $article['url'] ?? '#' }}"
                                class="healthcare-resource-card min-w-[250px] snap-start rounded-xl border border-stone-200 bg-white p-6 transition hover:-translate-y-1 hover:border-[#0f766e] hover:shadow-lg md:min-w-0"
                            >
                                <p
                                    class="text-xs font-black tracking-widest text-[#0f766e] uppercase"
                                >
                                    {{ $article['type'] ?? __('capell-theme-healthcare::generic.article_label') }}
                                </p>
                                <h3 class="mt-4 text-xl font-black">
                                    {{ $article['title'] }}
                                </h3>
                                <p class="mt-3 text-sm">
                                    {{ $article['summary'] ?? '' }}
                                </p>
                            </a>
                        @else
                            <article
                                class="healthcare-resource-card rounded-xl border border-stone-200 bg-white p-6"
                            >
                                <p
                                    class="text-xs font-black tracking-widest text-[#0f766e] uppercase"
                                >
                                    {{ __('capell-theme-healthcare::generic.resource') }}
                                </p>
                                <h3 class="mt-4 text-xl font-black">
                                    {{ $article['title'] }}
                                </h3>
                                <p class="mt-3 text-sm">
                                    {{ $article['summary'] ?? '' }}
                                </p>
                            </article>
                        @endif
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="Previous articles"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="Next articles"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </section>
@endif

<script>
    document
        .querySelectorAll('[data-carousel="healthcare-blog-teaser"]')
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

            const scrollByAmount = (direction) => {
                track.scrollBy({
                    left: direction * step(),
                    behavior: 'smooth',
                })
            }

            prev.addEventListener('click', () => scrollByAmount(-1))
            next.addEventListener('click', () => scrollByAmount(1))
            track.addEventListener('scroll', updateButtons, { passive: true })
            window.addEventListener('resize', updateButtons)

            updateButtons()
        })
</script>
