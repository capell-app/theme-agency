@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-commerce::generic.lookbook_label');
    $summary ??= $section->summary ?? null;
@endphp

<section class="theme-section theme-section-lookbook bg-[#fffaf3]">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.72fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#1f5f4a] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.lookbook_label') }}
                </p>
                <h2
                    class="mt-3 text-4xl font-black tracking-normal text-[#17211c]"
                >
                    {{ $heading }}
                </h2>
            </div>

            @if ($summary)
                <p class="text-base leading-8 text-stone-700">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @forelse ($items as $item)
                <article
                    class="grid min-h-full grid-rows-[auto_1fr] overflow-hidden border border-stone-200 bg-white shadow-sm"
                >
                    <div
                        class="grid aspect-[4/3] bg-[#17211c] p-4 text-white"
                        aria-hidden="true"
                    >
                        <span
                            class="self-end text-xs font-black tracking-[0.16em] text-[#e86f5c] uppercase"
                        >
                            {{ $item['type'] ?? __('capell-theme-commerce::generic.curated_label') }}
                        </span>
                    </div>
                    <div class="grid gap-3 p-5">
                        <h3 class="text-xl font-black text-[#17211c]">
                            {{ $item['title'] ?? __('capell-theme-commerce::generic.range_label') }}
                        </h3>
                        <p class="text-sm leading-6 text-stone-600">
                            {{ $item['summary'] ?? __('capell-theme-commerce::generic.lookbook_ready') }}
                        </p>
                    </div>
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
</section>
