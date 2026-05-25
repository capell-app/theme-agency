@php
    $shopifyAvailable ??= false;
    $items = $section->items ?? [];
@endphp

<section class="retail-catalog bg-white">
    <div class="grid gap-8 px-6 lg:grid-cols-[1fr_0.9fr] lg:items-center">
        <div>
            <h2>{{ $section->heading }}</h2>
            @if ($section->summary ?? null)
                <p class="mt-4 max-w-2xl text-lg">{{ $section->summary }}</p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @foreach ($items as $item)
                    <span
                        class="rounded-full border border-stone-200 bg-[#fffaf3] px-4 py-2 text-sm font-bold text-[#17211c]"
                    >
                        {{ $item['title'] ?? $item['label'] ?? '' }}
                    </span>
                @endforeach
            </div>
        </div>

        <div class="retail-frame bg-[#17211c] p-6 text-white">
            <p
                class="text-xs font-black tracking-widest text-[#e86f5c] uppercase"
            >
                {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_ready') : __('capell-theme-commerce::generic.catalog_ready') }}
            </p>
            <h3 class="mt-4 text-2xl font-black text-white">
                {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_panel') : __('capell-theme-commerce::generic.catalog_panel') }}
            </h3>
            <p class="mt-3 text-sm text-stone-200">
                {{ $shopifyAvailable ? __('capell-theme-commerce::generic.shopify_summary') : __('capell-theme-commerce::generic.catalog_summary') }}
            </p>
        </div>
    </div>
</section>
