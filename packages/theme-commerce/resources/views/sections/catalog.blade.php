@php
    $shopifyAvailable ??= false;
    $items = $section->items ?? [];
    $catalog = $section->shopifySummary ?? $section->catalog ?? [];
    $catalog = is_array($catalog) ? $catalog : [];
    $catalogStats = [
        __('capell-theme-commerce::generic.shopify_products_synced') => $catalog['productsSynced'] ?? $catalog['product_count'] ?? $catalog['products'] ?? null,
        __('capell-theme-commerce::generic.shopify_variants_synced') => $catalog['variantsSynced'] ?? $catalog['variant_count'] ?? $catalog['variants'] ?? null,
        __('capell-theme-commerce::generic.shopify_available_stock') => $catalog['availableStock'] ?? $catalog['available_stock'] ?? $catalog['available'] ?? null,
    ];
    $hasCatalogStats = collect($catalogStats)->filter(static fn (mixed $value): bool => $value !== null && $value !== '')->isNotEmpty();
@endphp

<section class="retail-catalog bg-white">
    <div
        class="grid min-w-0 gap-8 px-6 lg:grid-cols-[1fr_0.9fr] lg:items-center"
    >
        <div class="min-w-0">
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

            <div
                class="theme-carousel relative mt-8 max-w-full min-w-0 overflow-hidden"
                data-carousel="catalog"
            >
                <p
                    class="sr-only"
                    aria-live="polite"
                    data-carousel-status
                    data-carousel-scrollable-label="{{ __('capell-theme-commerce::generic.carousel_scrollable') }}"
                    data-carousel-static-label="{{ __('capell-theme-commerce::generic.carousel_static') }}"
                >
                    {{ __('capell-theme-commerce::generic.carousel_static') }}
                </p>

                <div
                    class="flex max-w-full snap-x snap-mandatory [scrollbar-width:none] gap-3 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($items as $item)
                        <span
                            class="min-w-[220px] snap-start rounded-full border border-stone-200 bg-[var(--retail-surface)] px-4 py-2 text-sm font-bold text-[var(--retail-ink)]"
                        >
                            {{ $item['title'] ?? $item['label'] ?? '' }}
                        </span>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="theme-carousel-button carousel-prev absolute top-1/2 left-2 -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="{{ __('capell-theme-commerce::generic.carousel_previous') }}"
                    aria-disabled="true"
                    data-carousel-prev
                >
                    <span aria-hidden="true">←</span>
                    <span class="sr-only">
                        {{ __('capell-theme-commerce::generic.carousel_previous') }}
                    </span>
                </button>
                <button
                    type="button"
                    class="theme-carousel-button carousel-next absolute top-1/2 right-2 -translate-y-1/2 rounded-full border border-stone-200 bg-white p-2 text-sm font-semibold shadow-md"
                    aria-label="{{ __('capell-theme-commerce::generic.carousel_next') }}"
                    aria-disabled="true"
                    data-carousel-next
                >
                    <span aria-hidden="true">→</span>
                    <span class="sr-only">
                        {{ __('capell-theme-commerce::generic.carousel_next') }}
                    </span>
                </button>
            </div>

            <div
                class="mt-8 rounded-xl border border-stone-200 bg-[var(--retail-ink)] p-6 text-white"
            >
                <p
                    class="text-xs font-black tracking-[0.16em] text-[var(--retail-accent)] uppercase"
                >
                    {{ $shopifyAvailable ? __('capell-theme-commerce::generic.catalog_connected') : __('capell-theme-commerce::generic.catalog_ready') }}
                </p>
                <h3 class="mt-3 text-2xl font-black">
                    {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_panel') : __('capell-theme-commerce::generic.catalog_panel') }}
                </h3>
                <p class="mt-3 text-stone-200">
                    {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_summary') : __('capell-theme-commerce::generic.catalog_summary') }}
                </p>

                @if ($hasCatalogStats)
                    <dl class="mt-5 grid gap-3 sm:grid-cols-3">
                        @foreach ($catalogStats as $label => $value)
                            @if ($value !== null && $value !== '')
                                <div>
                                    <dt
                                        class="text-xs font-black text-white/60 uppercase"
                                    >
                                        {{ $label }}
                                    </dt>
                                    <dd class="mt-1 text-lg font-black">
                                        {{ $value }}
                                    </dd>
                                </div>
                            @endif
                        @endforeach
                    </dl>
                @endif

                @if ($catalog['syncedAt'] ?? $catalog['synced_at'] ?? null)
                    <p class="mt-4 text-xs font-bold text-white/60">
                        {{ __('capell-theme-commerce::generic.shopify_synced_at') }}:
                        {{ $catalog['syncedAt'] ?? $catalog['synced_at'] }}
                    </p>
                @endif
            </div>
        </div>

        <div class="retail-frame bg-[var(--retail-ink)] p-6 text-white">
            <p
                class="text-xs font-black tracking-widest text-[var(--retail-accent)] uppercase"
            >
                {{ __('capell-theme-commerce::generic.catalog_highlights_label') }}
            </p>
            <h3 class="mt-4 text-2xl font-black text-white">
                {{ __('capell-theme-commerce::generic.catalog_highlights_heading') }}
            </h3>
            <p class="mt-3 text-sm text-stone-200">
                {{ __('capell-theme-commerce::generic.catalog_highlights_summary') }}
            </p>
        </div>
    </div>
</section>
