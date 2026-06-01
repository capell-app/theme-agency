@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-commerce::generic.buying_guide_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-buying-guide bg-[#fffaf3]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-6 md:grid-cols-[0.8fr_1.2fr]">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#e86f5c] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.buying_guide_label') }}
                </p>
                <h2
                    class="mt-3 text-4xl font-black tracking-normal text-[#17211c]"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-4 text-base leading-8 text-stone-700">
                        {{ $summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4">
                @forelse ($items as $item)
                    <article
                        class="grid gap-3 border border-stone-200 bg-white p-5"
                    >
                        <p class="text-xs font-black text-[#1f5f4a] uppercase">
                            {{ $item['type'] ?? __('capell-theme-commerce::generic.article_label') }}
                        </p>
                        <h3 class="text-xl font-black text-[#17211c]">
                            {{ $item['title'] ?? __('capell-theme-commerce::generic.resource') }}
                        </h3>
                        <p class="text-sm leading-6 text-stone-600">
                            {{ $item['summary'] ?? __('capell-theme-commerce::generic.buying_guide_static') }}
                        </p>
                    </article>
                @empty
                    <article
                        class="border border-dashed border-stone-300 bg-white p-6"
                    >
                        <h3 class="text-lg font-black text-[#17211c]">
                            {{ __('capell-theme-commerce::generic.premium_layout_ready') }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-stone-600">
                            {{ __('capell-theme-commerce::generic.premium_layout_empty') }}
                        </p>
                    </article>
                @endforelse
            </div>
        </div>
    </div>
</section>
