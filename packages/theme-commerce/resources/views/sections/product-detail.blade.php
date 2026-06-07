@php
    $product = $section->product ?? $section->shopifyProduct ?? [];
    $product = is_array($product) ? $product : [];
    $featuredImage = $product['featuredImage'] ?? $product['featured_image'] ?? [];
    $gallery = $section->gallery ?? $section->media ?? $product['gallery'] ?? $product['media'] ?? $product['images'] ?? [];
    $variants = $section->variants ?? $section->options ?? $product['variants'] ?? $product['options'] ?? [];
    $recommendations = $section->recommendations ?? $section->related ?? [];
    $firstVariant = is_countable($variants) && count($variants) > 0 && is_array($variants[0] ?? null) ? $variants[0] : [];
    $formatShopifyPrice = static function (array $variant): ?string {
        $amount = $variant['price'] ?? $variant['priceAmount'] ?? $variant['price_amount'] ?? null;
        $currency = $variant['priceCurrency'] ?? $variant['price_currency'] ?? null;

        if ($amount === null) {
            return null;
        }

        return trim((string) $currency . ' ' . (string) $amount);
    };
    $productAvailable = $section->availableForSale ?? $section->available_for_sale ?? $product['availableForSale'] ?? $product['available_for_sale'] ?? $firstVariant['availableForSale'] ?? $firstVariant['available_for_sale'] ?? null;
    $stockStatus = $section->stockStatus ?? $section->stock ?? (is_bool($productAvailable) ? ($productAvailable ? __('capell-theme-commerce::generic.shopify_in_stock') : __('capell-theme-commerce::generic.shopify_sold_out')) : null);
    $heading = $section->heading ?? $product['title'] ?? null;
    $summary = $section->summary ?? $product['description'] ?? $product['summary'] ?? null;
    $price = $section->price ?? $section->formattedPrice ?? $product['price'] ?? $product['formattedPrice'] ?? $formatShopifyPrice($firstVariant);
    $compareAtPrice = $section->compareAtPrice ?? $section->compareAt ?? $product['compareAtPrice'] ?? $product['compareAt'] ?? $firstVariant['compareAtPrice'] ?? $firstVariant['compare_at_price'] ?? null;
    $productImage = $section->image ?? $section->imageUrl ?? $featuredImage['url'] ?? $featuredImage['src'] ?? null;
    $productImageAlt = $section->imageAlt ?? $featuredImage['altText'] ?? $featuredImage['alt'] ?? $heading ?? '';
    $ctaLabel = $section->ctaLabel ?? __('capell-theme-commerce::generic.product_add_to_basket');
    $ctaUrl = $section->ctaUrl ?? $section->url ?? (isset($product['handle']) ? '/products/' . $product['handle'] : '#');
    $trustItems = $section->trustItems ?? [
        __('capell-theme-commerce::generic.product_shipping_label'),
        __('capell-theme-commerce::generic.product_returns_label'),
        __('capell-theme-commerce::generic.product_secure_checkout_label'),
    ];
    $variantLabel = static function (mixed $variant): string {
        if (! is_array($variant)) {
            return (string) $variant;
        }

        $selectedOptions = $variant['selectedOptions'] ?? $variant['selected_options'] ?? [];

        if (is_countable($selectedOptions) && count($selectedOptions) > 0) {
            return collect($selectedOptions)
                ->map(static fn (mixed $option): string => is_array($option) ? (string) ($option['value'] ?? $option['label'] ?? '') : (string) $option)
                ->filter()
                ->implode(' / ');
        }

        return (string) ($variant['label'] ?? $variant['name'] ?? $variant['title'] ?? '');
    };
@endphp

<section class="retail-product-detail bg-[var(--retail-surface)]">
    <div
        class="grid gap-8 px-6 lg:grid-cols-[minmax(0,1.08fr)_minmax(22rem,0.92fr)] lg:items-start"
    >
        <div>
            <p
                class="text-xs font-black tracking-[0.18em] text-[var(--retail-primary)] uppercase"
            >
                {{ __('capell-theme-commerce::generic.product_detail_label') }}
            </p>

            <div class="mt-5 grid gap-3 md:grid-cols-[1fr_0.36fr]">
                <div
                    class="overflow-hidden rounded-2xl border border-stone-200 bg-white"
                >
                    @if (($gallery[0]['url'] ?? $gallery[0]['image'] ?? null) || $productImage)
                        <img
                            src="{{ $gallery[0]['url'] ?? $gallery[0]['image'] ?? $productImage }}"
                            alt="{{ $gallery[0]['alt'] ?? $gallery[0]['imageAlt'] ?? $productImageAlt }}"
                            width="1200"
                            height="1200"
                            loading="eager"
                            decoding="async"
                            fetchpriority="high"
                            sizes="(min-width: 1024px) 52vw, 100vw"
                            class="aspect-square w-full object-cover"
                        />
                    @else
                        <div
                            class="flex aspect-square items-end rounded-2xl bg-[var(--retail-ink)] p-6 text-white"
                        >
                            <div class="w-full">
                                <p
                                    class="text-xs font-black tracking-[0.18em] text-[var(--retail-accent)] uppercase"
                                >
                                    {{ __('capell-theme-commerce::generic.product_gallery_label') }}
                                </p>
                                <div class="mt-20 grid grid-cols-3 gap-3">
                                    <span
                                        class="h-16 rounded-xl bg-white/15"
                                    ></span>
                                    <span
                                        class="h-16 rounded-xl bg-[var(--retail-primary)]"
                                    ></span>
                                    <span
                                        class="h-16 rounded-xl bg-[var(--retail-accent)]"
                                    ></span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="grid gap-3">
                    @forelse (array_slice($gallery, 1, 3) as $media)
                        <div
                            class="overflow-hidden rounded-xl border border-stone-200 bg-white"
                        >
                            @if ($media['url'] ?? $media['image'] ?? null)
                                <img
                                    src="{{ $media['url'] ?? $media['image'] }}"
                                    alt="{{ $media['alt'] ?? $media['imageAlt'] ?? '' }}"
                                    width="420"
                                    height="420"
                                    loading="lazy"
                                    decoding="async"
                                    sizes="(min-width: 1024px) 14vw, 30vw"
                                    class="aspect-square w-full object-cover"
                                />
                            @endif
                        </div>
                    @empty
                        @foreach ([1, 2, 3] as $placeholder)
                            <div
                                class="rounded-xl border border-stone-200 bg-white p-4"
                                aria-hidden="true"
                            >
                                <span
                                    class="block aspect-square rounded-lg bg-[var(--retail-ink)]/10"
                                ></span>
                            </div>
                        @endforeach
                    @endforelse
                </div>
            </div>
        </div>

        <div class="retail-frame bg-white p-6 shadow-sm">
            @if ($heading)
                <h2
                    class="text-4xl font-black tracking-tight text-[var(--retail-ink)]"
                >
                    {{ $heading }}
                </h2>
            @else
                <h2
                    class="text-3xl font-black tracking-tight text-[var(--retail-ink)]"
                >
                    {{ __('capell-theme-commerce::generic.product_detail_empty_title') }}
                </h2>
                <p class="mt-3 text-sm text-stone-600">
                    {{ __('capell-theme-commerce::generic.product_detail_empty_summary') }}
                </p>
            @endif

            @if ($summary)
                <p class="mt-4 text-lg text-stone-600">
                    {{ $summary }}
                </p>
            @endif

            <div class="mt-6 flex flex-wrap items-end gap-3">
                @if ($price)
                    <p class="text-3xl font-black text-[var(--retail-ink)]">
                        {{ $price }}
                    </p>
                @endif

                @if ($compareAtPrice)
                    <p class="text-sm font-bold text-stone-500 line-through">
                        <span class="sr-only">
                            {{ __('capell-theme-commerce::generic.product_compare_at_label') }}
                        </span>
                        {{ $compareAtPrice }}
                    </p>
                @endif

                @if ($stockStatus)
                    <p
                        class="rounded-full bg-[var(--retail-primary)]/10 px-3 py-1 text-xs font-black text-[var(--retail-primary)] uppercase"
                    >
                        {{ $stockStatus }}
                    </p>
                @endif
            </div>

            @if (is_countable($variants) && count($variants) > 0)
                <div class="mt-7">
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[var(--retail-primary)] uppercase"
                    >
                        {{ __('capell-theme-commerce::generic.product_variants_label') }}
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($variants as $variant)
                            <span
                                class="rounded-full border border-stone-200 bg-[var(--retail-surface)] px-4 py-2 text-sm font-bold text-[var(--retail-ink)]"
                            >
                                {{ $variantLabel($variant) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-7 grid gap-3">
                <a
                    href="{{ $ctaUrl }}"
                    class="inline-flex justify-center rounded-full bg-[var(--retail-accent)] px-6 py-3 text-sm font-black text-white"
                >
                    {{ $ctaLabel }}
                </a>
                <div
                    class="rounded-xl border border-stone-200 bg-[var(--retail-surface)] p-4"
                >
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[var(--retail-primary)] uppercase"
                    >
                        {{ __('capell-theme-commerce::generic.product_stock_label') }}
                    </p>
                    <div
                        class="mt-3 grid gap-2 text-sm font-bold text-stone-700"
                    >
                        @foreach ($trustItems as $trustItem)
                            <p>
                                {{ is_array($trustItem) ? $trustItem['label'] ?? '' : $trustItem }}
                            </p>
                        @endforeach
                    </div>
                </div>
            </div>

            @if (is_countable($recommendations) && count($recommendations) > 0)
                <div class="mt-8 border-t border-stone-200 pt-6">
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[var(--retail-primary)] uppercase"
                    >
                        {{ __('capell-theme-commerce::generic.product_recommendations_label') }}
                    </p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach (array_slice($recommendations, 0, 2) as $recommendation)
                            <a
                                href="{{ $recommendation['url'] ?? '#' }}"
                                class="rounded-xl border border-stone-200 bg-[var(--retail-surface)] p-4 text-sm font-bold text-[var(--retail-ink)]"
                            >
                                <span>
                                    {{ $recommendation['title'] ?? $recommendation['label'] ?? '' }}
                                </span>
                                @if ($recommendation['price'] ?? null)
                                    <span
                                        class="mt-2 block text-[var(--retail-primary)]"
                                    >
                                        {{ $recommendation['price'] }}
                                    </span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
