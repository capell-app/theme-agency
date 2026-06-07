@php
    $items ??= $section->items ?? [];
    $heading ??= $section->heading ?? __('capell-theme-commerce::generic.promotion_label');
    $summary ??= $section->summary ?? null;
    $countdown = $section->countdown ?? $section->endsIn ?? $section->expiresAt ?? null;
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

                @if ($countdown)
                    <div class="mt-6 rounded-2xl bg-white p-4 text-[#17211c]">
                        <p class="text-xs font-black text-stone-500 uppercase">
                            {{ __('capell-theme-commerce::generic.promotion_countdown_label') }}
                        </p>
                        @if (is_iterable($countdown))
                            <div class="mt-3 grid grid-cols-3 gap-2">
                                @foreach ($countdown as $countdownItem)
                                    <div
                                        class="rounded-xl bg-[#fffaf3] px-3 py-2"
                                    >
                                        <p
                                            class="text-lg font-black text-[#e86f5c]"
                                        >
                                            {{ $countdownItem['value'] ?? $countdownItem['count'] ?? '' }}
                                        </p>
                                        <p
                                            class="text-[0.68rem] font-black text-stone-500 uppercase"
                                        >
                                            {{ $countdownItem['label'] ?? '' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2 text-lg font-black text-[#e86f5c]">
                                {{ $countdown }}
                            </p>
                        @endif
                    </div>
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
                        @if ($item['countdown'] ?? $item['endsIn'] ?? null)
                            <p class="text-xs font-black text-white uppercase">
                                {{ __('capell-theme-commerce::generic.promotion_countdown_label') }}:
                                {{ $item['countdown'] ?? $item['endsIn'] }}
                            </p>
                        @endif

                        @if (($item['code'] ?? null) || ($item['discount'] ?? null))
                            <p class="text-sm font-black text-white">
                                {{ $item['code'] ?? $item['discount'] }}
                            </p>
                        @endif
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
