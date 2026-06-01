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

        <div
            class="theme-carousel relative mt-10"
            data-carousel="product-grid"
        >
            <div
                class="{{ $usesCarousel ? 'flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:overflow-visible md:pr-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden' : 'grid gap-4 md:grid-cols-2 lg:grid-cols-3' }}"
                data-carousel-track
            >
                @foreach ($products as $product)
                    <article
                        class="{{ $usesCarousel ? 'min-w-[240px] snap-start sm:min-w-[260px] lg:min-w-[280px]' : '' }} group rounded-xl border border-stone-200 bg-[#fffaf3] p-3 transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        @if ($product['image'] ?? $product['imageUrl'] ?? null)
                            <div class="overflow-hidden rounded-lg">
                                <img
                                    src="{{ $product['image'] ?? $product['imageUrl'] }}"
                                    alt="{{ $product['imageAlt'] ?? '' }}"
                                    class="aspect-square w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                            </div>
                        @else
                            <div
                                class="retail-product-placeholder rounded-lg border border-[#e8ddd0] bg-[#17211c] p-4 text-white"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <p
                                        class="text-xs font-black text-[#f6e6d7] uppercase"
                                    >
                                        {{ $product['icon'] ?? $product['type'] ?? __('capell-theme-commerce::generic.product_label') }}
                                    </p>
                                    <span
                                        class="rounded-full bg-[#e86f5c] px-2 py-1 text-xs font-black text-white"
                                    >
                                        {{ $product['price'] ?? $product['metric'] ?? __('capell-theme-commerce::generic.range_label') }}
                                    </span>
                                </div>
                                <div
                                    class="mt-8 grid grid-cols-[1fr_0.72fr] gap-2"
                                >
                                    <div
                                        class="rounded-md bg-[#f8eee3] p-3 text-[#17211c]"
                                    >
                                        <p class="text-xs font-black uppercase">
                                            {{ __('capell-theme-commerce::generic.stock_label') }}
                                        </p>
                                        <span
                                            class="mt-5 block h-2 rounded-full bg-[#1f5f4a]"
                                        ></span>
                                    </div>
                                    <div class="rounded-md bg-[#1f5f4a] p-3">
                                        <p class="text-xs font-black uppercase">
                                            {{ __('capell-theme-commerce::generic.basket_label') }}
                                        </p>
                                        <span
                                            class="mt-5 block h-2 rounded-full bg-[#e86f5c]"
                                        ></span>
                                    </div>
                                </div>
                                <div
                                    class="mt-3 grid grid-cols-3 gap-2"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="h-8 rounded-md bg-white/15"
                                    ></span>
                                    <span
                                        class="h-8 rounded-md bg-[#f8eee3]/70"
                                    ></span>
                                    <span
                                        class="h-8 rounded-md bg-[#e86f5c]/80"
                                    ></span>
                                </div>
                            </div>
                        @endif
                        <div class="p-2">
                            <p
                                class="mb-2 text-xs font-black text-[#1f5f4a] uppercase"
                            >
                                {{ __('capell-theme-commerce::generic.buying_path_label') }}
                            </p>
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

                            <p
                                class="mt-4 text-xs font-black text-[#e86f5c] uppercase"
                            >
                                {{ __('capell-theme-commerce::generic.merchandising_note_label') }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            <button
                type="button"
                class="theme-carousel-button carousel-prev absolute top-1/2 left-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-commerce::generic.carousel_previous') }}"
                data-carousel-prev
            >
                ‹
            </button>
            <button
                type="button"
                class="theme-carousel-button carousel-next absolute top-1/2 right-2 hidden -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                aria-label="{{ __('capell-theme-commerce::generic.carousel_next') }}"
                data-carousel-next
            >
                ›
            </button>
        </div>
    </div>
</section>
