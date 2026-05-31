@php
    $shopifyAvailable ??= false;
    $items = $section->items ?? [];
@endphp

<section class="retail-catalog bg-white">
    <div
        class="grid min-w-0 gap-8 px-6 lg:grid-cols-[1fr_0.9fr] lg:items-center"
    >
        <div class="min-w-0">
            <h2 class="text-4xl font-black tracking-tight text-[#17211c]">
                {{ $section->heading }}
            </h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-2xl text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif

            <div
                class="theme-carousel relative mt-8 max-w-full min-w-0 overflow-hidden"
                data-carousel="commerce-catalog"
            >
                <div
                    class="flex max-w-full snap-x snap-mandatory [scrollbar-width:none] gap-3 overflow-x-auto pr-6 pb-2 sm:grid sm:grid-cols-3 [&::-webkit-scrollbar]:hidden"
                    data-carousel-track
                >
                    @foreach ($items as $item)
                        <span
                            class="min-w-[220px] snap-start rounded-full border border-stone-200 bg-[#fffaf3] px-4 py-2 text-sm font-bold text-[#17211c]"
                        >
                            {{ $item['title'] ?? $item['label'] ?? '' }}
                        </span>
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

            <div
                class="mt-8 rounded-xl border border-stone-200 bg-[#17211c] p-6 text-white"
            >
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#e86f5c] uppercase"
                >
                    {{ $shopifyAvailable ? __('capell-theme-commerce::generic.catalog_connected') : __('capell-theme-commerce::generic.catalog_ready') }}
                </p>
                <h3 class="mt-3 text-2xl font-black">
                    {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_panel') : __('capell-theme-commerce::generic.catalog_panel') }}
                </h3>
                <p class="mt-3 text-stone-200">
                    {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_summary') : __('capell-theme-commerce::generic.catalog_summary') }}
                </p>
            </div>
        </div>

        <div class="retail-frame bg-[#17211c] p-6 text-white">
            <p
                class="text-xs font-black tracking-widest text-[#e86f5c] uppercase"
            >
                Highlights
            </p>
            <h3 class="mt-4 text-2xl font-black text-white">
                Conversion-ready merchandising
            </h3>
            <p class="mt-3 text-sm text-stone-200">
                Discover products by behavior, seasonality, and intent signals
                for stronger margin.
            </p>
        </div>
    </div>
</section>
