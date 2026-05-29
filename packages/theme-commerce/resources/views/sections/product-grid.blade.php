@php
    $products = $section->features ?? $section->items ?? [];
    $productCount = is_countable($products) ? count($products) : 0;
    $usesCarousel = $productCount > 4;
@endphp

<section class="retail-products bg-white">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-2xl text-lg text-stone-600">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>
        </div>

        <div class="theme-carousel relative mt-10" data-carousel="product-grid">
            <div
                class="{{ $usesCarousel ? 'flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:overflow-visible md:pr-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden' : 'grid gap-4 md:grid-cols-2 lg:grid-cols-3' }}"
                data-carousel-track
            >
                @foreach ($products as $product)
                    <article
                        class="{{ $usesCarousel ? 'min-w-[240px] snap-start sm:min-w-[260px] lg:min-w-[280px]' : '' }} group rounded-xl border border-stone-200 bg-[#fffaf3] p-3 transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        @if ($product['image'] ?? $product['imageUrl'] ?? null)
                            <img
                                src="{{ $product['image'] ?? $product['imageUrl'] }}"
                                alt="{{ $product['imageAlt'] ?? '' }}"
                                class="aspect-square w-full rounded-lg object-cover transition duration-500 group-hover:scale-105"
                            />
                        @else
                            <div
                                class="retail-product-placeholder flex h-28 items-end rounded-lg border border-[#e8ddd0] bg-[#17211c] p-4 text-white"
                            >
                                <div>
                                    <p
                                        class="text-[0.65rem] font-black tracking-widest text-[#f6e6d7] uppercase"
                                    >
                                        {{ $product['icon'] ?? $product['type'] ?? __('capell-theme-commerce::generic.product_label') }}
                                    </p>
                                    <p
                                        class="mt-1 text-sm font-black text-white"
                                    >
                                        {{ $product['metric'] ?? __('capell-theme-commerce::generic.curated_label') }}
                                    </p>
                                </div>
                            </div>
                        @endif
                        <div class="p-2">
                            <h3 class="text-lg font-black">
                                {{ $product['title'] }}
                            </h3>
                            <p class="mt-2 text-sm">
                                {{ $product['description'] ?? $product['summary'] ?? '' }}
                            </p>
                            @if ($product['price'] ?? $product['metric'] ?? null)
                                <p
                                    class="mt-4 text-sm font-black text-[#1f5f4a]"
                                >
                                    {{ $product['price'] ?? $product['metric'] }}
                                </p>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Previous products"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="Next products"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>

<script>
    document
        .querySelectorAll('[data-carousel="product-grid"]')
        .forEach((carousel) => {
            const track = carousel.querySelector('[data-carousel-track]')
            const prev = carousel.querySelector('[data-carousel-prev]')
            const next = carousel.querySelector('[data-carousel-next]')

            if (!track || !prev || !next) {
                return
            }

            const step = () =>
                Math.max(260, Math.floor(track.clientWidth * 0.85))

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
