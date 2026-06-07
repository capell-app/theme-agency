@php
    $products = $section->features ?? $section->items ?? [];
    $productCount = is_countable($products) ? count($products) : 0;
    $usesCarousel = $productCount > 4;

    $formatShopifyPrice = static function (array $variant): ?string {
        $amount = $variant['price'] ?? $variant['priceAmount'] ?? $variant['price_amount'] ?? null;
        $currency = $variant['priceCurrency'] ?? $variant['price_currency'] ?? null;

        if ($amount === null) {
            return null;
        }

        return trim((string) $currency . ' ' . (string) $amount);
    };

    $shopifyVariantLabel = static function (array $variant): string {
        $selectedOptions = $variant['selectedOptions'] ?? $variant['selected_options'] ?? [];

        if (is_countable($selectedOptions) && count($selectedOptions) > 0) {
            return collect($selectedOptions)
                ->map(static fn (mixed $option): string => is_array($option) ? (string) ($option['value'] ?? $option['label'] ?? '') : (string) $option)
                ->filter()
                ->implode(' / ');
        }

        return (string) ($variant['title'] ?? $variant['label'] ?? $variant['name'] ?? '');
    };
@endphp

<section class="retail-products bg-white">
    <div class="px-6">
        <div
            class="flex flex-col justify-between gap-5 md:flex-row md:items-end"
        >
            <div>
                <h2
                    class="text-4xl font-black tracking-tight text-[var(--retail-ink)]"
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
            data-carousel="product-grid"
        >
            <div
                class="{{ $usesCarousel ? 'flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-6 pb-2 md:grid md:grid-cols-3 md:overflow-visible md:pr-0 lg:grid-cols-4 [&::-webkit-scrollbar]:hidden' : 'grid gap-4 md:grid-cols-2 lg:grid-cols-3' }}"
                data-carousel-track
            >
                @foreach ($products as $product)
                    @php
                        $featuredImage = $product['featuredImage'] ?? $product['featured_image'] ?? [];
                        $variants = $product['variants'] ?? [];
                        $firstVariant = is_countable($variants) && count($variants) > 0 && is_array($variants[0] ?? null) ? $variants[0] : [];
                        $productImage = $product['image'] ?? $product['imageUrl'] ?? $featuredImage['url'] ?? $featuredImage['src'] ?? null;
                        $productImageAlt = $product['imageAlt'] ?? $featuredImage['altText'] ?? $featuredImage['alt'] ?? '';
                        $productPrice = $product['price'] ?? $product['formattedPrice'] ?? $product['metric'] ?? $formatShopifyPrice($firstVariant);
                        $productAvailable = $product['availableForSale'] ?? $product['available_for_sale'] ?? $firstVariant['availableForSale'] ?? $firstVariant['available_for_sale'] ?? null;
                        $stockStatus = $product['stockStatus'] ?? $product['stock'] ?? (is_bool($productAvailable) ? ($productAvailable ? __('capell-theme-commerce::generic.shopify_in_stock') : __('capell-theme-commerce::generic.shopify_sold_out')) : null);
                    @endphp

                    <article
                        class="{{ $usesCarousel ? 'min-w-[240px] snap-start sm:min-w-[260px] lg:min-w-[280px]' : '' }} group rounded-xl border border-stone-200 bg-[var(--retail-surface)] p-3 transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        @if ($productImage)
                            <div class="overflow-hidden rounded-lg">
                                <img
                                    src="{{ $productImage }}"
                                    alt="{{ $productImageAlt }}"
                                    width="800"
                                    height="800"
                                    loading="lazy"
                                    decoding="async"
                                    sizes="(min-width: 1024px) 25vw, (min-width: 768px) 33vw, 80vw"
                                    class="aspect-square w-full object-cover transition duration-500 group-hover:scale-105"
                                />
                            </div>
                        @else
                            <div
                                class="retail-product-placeholder rounded-lg border border-[var(--retail-line)] bg-[var(--retail-ink)] p-4 text-white"
                            >
                                <div
                                    class="flex items-center justify-between gap-3"
                                >
                                    <p
                                        class="text-xs font-black text-[var(--retail-warm)] uppercase"
                                    >
                                        {{ $product['icon'] ?? $product['type'] ?? __('capell-theme-commerce::generic.product_label') }}
                                    </p>
                                    <span
                                        class="rounded-full bg-[var(--retail-accent)] px-2 py-1 text-xs font-black text-white"
                                    >
                                        {{ $productPrice ?? __('capell-theme-commerce::generic.range_label') }}
                                    </span>
                                </div>
                                <div
                                    class="mt-8 grid grid-cols-[1fr_0.72fr] gap-2"
                                >
                                    <div
                                        class="rounded-md bg-[var(--retail-panel)] p-3 text-[var(--retail-ink)]"
                                    >
                                        <p class="text-xs font-black uppercase">
                                            {{ __('capell-theme-commerce::generic.stock_label') }}
                                        </p>
                                        <span
                                            class="mt-5 block h-2 rounded-full bg-[var(--retail-primary)]"
                                        ></span>
                                    </div>
                                    <div
                                        class="rounded-md bg-[var(--retail-primary)] p-3"
                                    >
                                        <p class="text-xs font-black uppercase">
                                            {{ __('capell-theme-commerce::generic.basket_label') }}
                                        </p>
                                        <span
                                            class="mt-5 block h-2 rounded-full bg-[var(--retail-accent)]"
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
                                        class="h-8 rounded-md bg-[var(--retail-panel)]/70"
                                    ></span>
                                    <span
                                        class="h-8 rounded-md bg-[var(--retail-accent)]/80"
                                    ></span>
                                </div>
                            </div>
                        @endif
                        <div class="p-2">
                            <p
                                class="mb-2 text-xs font-black text-[var(--retail-primary)] uppercase"
                            >
                                {{ __('capell-theme-commerce::generic.buying_path_label') }}
                            </p>
                            <h3 class="text-lg font-black">
                                {{ $product['title'] }}
                            </h3>
                            <p class="mt-2 text-sm">
                                {{ $product['description'] ?? $product['summary'] ?? '' }}
                            </p>
                            @if ($productPrice)
                                <p
                                    class="mt-4 text-sm font-black text-[var(--retail-primary)]"
                                >
                                    {{ $productPrice }}
                                </p>
                            @endif

                            @if ($stockStatus)
                                <p
                                    class="mt-3 text-xs font-black text-[var(--retail-primary)] uppercase"
                                >
                                    {{ $stockStatus }}
                                </p>
                            @endif

                            @if (is_countable($variants) && count($variants) > 0)
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @foreach (array_slice($variants, 0, 3) as $variant)
                                        @if (is_array($variant) && $shopifyVariantLabel($variant) !== '')
                                            <span
                                                class="rounded-full border border-stone-200 px-2.5 py-1 text-xs font-bold text-[var(--retail-ink)]"
                                            >
                                                {{ $shopifyVariantLabel($variant) }}
                                            </span>
                                        @endif
                                    @endforeach
                                </div>
                            @endif

                            <p
                                class="mt-4 text-xs font-black text-[var(--retail-accent)] uppercase"
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
