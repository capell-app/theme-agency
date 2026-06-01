@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-commerce::generic.promotion_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-promotion bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div
            class="grid gap-8 border border-[#1f5f4a]/20 bg-[#1f5f4a] p-8 text-white md:grid-cols-[0.78fr_1fr] md:items-center"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#f6e6d7] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.promotion_label') }}
                </p>
                <h2 class="mt-3 text-4xl font-black tracking-normal">
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-white/80">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-3">
                @forelse ($items as $item)
                    <article
                        class="grid gap-2 border border-white/20 bg-white/10 p-4"
                    >
                        <p class="text-xs font-black text-[#f6e6d7] uppercase">
                            {{ $item['type'] ?? __('capell-theme-commerce::generic.campaign_ready') }}
                        </p>
                        <h3 class="text-lg font-black">
                            {{ $item['title'] ?? __('capell-theme-commerce::generic.basket_label') }}
                        </h3>
                        <p class="text-sm leading-6 text-white/78">
                            {{ $item['summary'] ?? __('capell-theme-commerce::generic.catalog_summary') }}
                        </p>
                    </article>
                @empty
                    <article class="border border-white/20 bg-white/10 p-4">
                        <h3 class="text-lg font-black">
                            {{ __('capell-theme-commerce::generic.premium_layout_ready') }}
                        </h3>
                        <p class="mt-2 text-sm text-white/78">
                            {{ __('capell-theme-commerce::generic.premium_layout_empty') }}
                        </p>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
