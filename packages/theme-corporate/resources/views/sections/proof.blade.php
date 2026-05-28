<section
    class="theme-proof border-b border-slate-800 bg-slate-950 text-white dark:border-white/10 dark:bg-black"
>
    <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-12 lg:py-16">
        <div class="grid gap-6 lg:grid-cols-[0.8fr_1.2fr] lg:gap-8">
            <div>
                <p
                    class="mb-3 text-xs font-semibold tracking-[0.16em] text-[var(--theme-accent)] uppercase"
                >
                    Proof
                </p>
                <h2
                    class="max-w-xl text-2xl leading-tight font-semibold sm:text-3xl lg:text-4xl"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p
                        class="mt-3 max-w-md text-sm leading-6 text-slate-300 sm:leading-7"
                    >
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div
                class="theme-carousel relative"
                data-carousel="corporate-proof"
            >
                <div
                    class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-2 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($section->items as $item)
                        <figure
                            class="min-w-[260px] snap-start rounded-[0.35rem] border border-white/10 bg-white/[0.03] p-5 sm:p-6 md:min-w-0"
                        >
                            @if (! empty($item['image']))
                                <button
                                    type="button"
                                    class="mb-4 block w-full"
                                    data-gallery-open="{{ $item['image'] }}"
                                    aria-label="Open image for {{ $item['title'] ?? $item['name'] ?? $item['logo'] ?? 'proof item' }}"
                                >
                                    <img
                                        src="{{ $item['image'] }}"
                                        alt="{{ $item['title'] ?? $item['name'] ?? $item['logo'] ?? 'Proof image' }}"
                                        class="aspect-[5/3] w-full rounded-[0.25rem] object-cover opacity-90 transition hover:opacity-100 sm:mb-5 sm:aspect-[16/9]"
                                    />
                                </button>
                            @endif

                            <blockquote
                                class="text-sm leading-7 text-slate-200"
                            >
                                {{ $item['quote'] ?? $item['metric'] ?? $item['summary'] ?? '' }}
                            </blockquote>
                            <figcaption
                                class="mt-5 flex flex-wrap items-center gap-2 text-xs text-slate-400"
                            >
                                <span class="font-semibold text-white">
                                    {{ $item['title'] ?? $item['name'] ?? $item['logo'] ?? '' }}
                                </span>
                                @if (! empty($item['type']))
                                    <span class="text-slate-600">/</span>
                                    <span>{{ $item['type'] }}</span>
                                @endif

                                @if (! empty($item['publishedDate']))
                                    <span class="text-slate-600">/</span>
                                    <time
                                        datetime="{{ $item['publishedAt'] ?? '' }}"
                                    >
                                        {{ $item['publishedDate'] }}
                                    </time>
                                @endif
                            </figcaption>
                        </figure>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                    aria-label="Previous proof cards"
                    data-carousel-prev
                >
                    ‹
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-white/20 bg-white/10 p-2 text-sm font-semibold text-white shadow-md"
                    aria-label="Next proof cards"
                    data-carousel-next
                >
                    ›
                </button>
            </div>
        </div>
    </div>
</section>

<div
    class="gallery-lightbox fixed inset-0 z-50 hidden items-center justify-center bg-black/75 p-4"
    data-gallery-lightbox
>
    <button
        type="button"
        class="absolute top-4 right-4 rounded-full bg-white px-3 py-1 text-xs font-bold text-black"
        data-gallery-close
        aria-label="Close gallery"
    >
        ✕
    </button>
    <img
        src=""
        alt=""
        class="max-h-[85vh] w-full max-w-4xl rounded-xl border border-white/20 object-contain"
        data-gallery-image
    />
</div>

<script>
    document
        .querySelectorAll('[data-carousel="corporate-proof"]')
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

            const scrollByAmount = (direction) => {
                track.scrollBy({ left: direction * step(), behavior: 'smooth' })
            }

            prev.addEventListener('click', () => scrollByAmount(-1))
            next.addEventListener('click', () => scrollByAmount(1))
            track.addEventListener('scroll', updateButtons, { passive: true })
            window.addEventListener('resize', updateButtons)
            updateButtons()
        })

    document.querySelectorAll('[data-gallery-open]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const imageUrl = trigger.getAttribute('data-gallery-open')
            const lightbox = document.querySelector('[data-gallery-lightbox]')
            const lightboxImage = document.querySelector('[data-gallery-image]')

            if (!lightbox || !lightboxImage) {
                return
            }

            lightboxImage.src = imageUrl
            lightbox.classList.remove('hidden')
            lightbox.classList.add('flex')
        })
    })

    document
        .querySelector('[data-gallery-close]')
        ?.addEventListener('click', () => {
            const lightbox = document.querySelector('[data-gallery-lightbox]')
            lightbox?.classList.add('hidden')
            lightbox?.classList.remove('flex')
        })

    document
        .querySelector('[data-gallery-lightbox]')
        ?.addEventListener('click', (event) => {
            const lightbox = document.querySelector('[data-gallery-lightbox]')
            if (event.target === lightbox) {
                lightbox?.classList.add('hidden')
                lightbox?.classList.remove('flex')
            }
        })

    window.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            const lightbox = document.querySelector('[data-gallery-lightbox]')
            if (lightbox && !lightbox.classList.contains('hidden')) {
                lightbox.classList.add('hidden')
                lightbox.classList.remove('flex')
            }
        }
    })
</script>
