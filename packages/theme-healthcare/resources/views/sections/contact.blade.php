@php
    $locations = $section->locations ?? $section->items ?? [];
@endphp

<section class="healthcare-contact bg-[#f6fbfd]">
    <div class="grid gap-8 px-6 lg:grid-cols-[0.9fr_1.1fr]">
        <div>
            <h2 class="text-4xl font-black tracking-tight text-[#14323a]">
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div
            class="theme-carousel relative mt-2"
            data-carousel="healthcare-contact"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-2 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @foreach ($locations as $location)
                    <article
                        class="min-w-[260px] snap-start rounded-lg border border-[#d9e8ee] bg-white p-6"
                    >
                        <p
                            class="text-xs font-black tracking-widest text-[#2563eb] uppercase"
                        >
                            {{ $location['label'] ?? __('capell-theme-healthcare::generic.location') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black">
                            {{ $location['title'] ?? '' }}
                        </h3>
                        <p class="mt-2 text-sm">
                            {{ $location['summary'] ?? $location['address'] ?? '' }}
                        </p>
                        @if ($location['phone'] ?? null)
                            <p class="mt-4 text-sm font-black text-[#0f766e]">
                                {{ $location['phone'] }}
                            </p>
                        @endif
                    </article>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-[#d9e8ee] bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Previous locations"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-[#d9e8ee] bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Next locations"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="healthcare-contact"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(250, Math.floor(track.clientWidth * 0.8))

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
