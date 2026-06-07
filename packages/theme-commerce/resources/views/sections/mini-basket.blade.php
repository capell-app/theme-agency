@php
    $items = $section->items ?? $section->lineItems ?? [];
    $subtotal = $section->subtotal ?? $section->total ?? null;
    $checkoutUrl = $section->checkoutUrl ?? $section->url ?? '#';
    $checkoutLabel = $section->checkoutLabel ?? __('capell-theme-commerce::generic.basket_checkout_label');
    $deliveryNote = $section->deliveryNote ?? $section->delivery ?? null;
@endphp

<section class="retail-mini-basket bg-white">
    <div class="px-6">
        <div
            class="grid gap-8 rounded-2xl border border-stone-200 bg-[#fffaf3] p-6 lg:grid-cols-[0.72fr_1.28fr]"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#1f5f4a] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.basket_label') }}
                </p>
                <h2 class="mt-4 text-3xl font-black text-[#17211c]">
                    {{ $section->heading ?? __('capell-theme-commerce::generic.basket_empty_title') }}
                </h2>
                <p class="mt-3 text-sm leading-6 text-stone-600">
                    {{ $section->summary ?? __('capell-theme-commerce::generic.basket_empty_summary') }}
                </p>
            </div>

            <div class="rounded-2xl bg-white p-4 shadow-sm">
                <div class="grid gap-3">
                    @forelse ($items as $item)
                        <article
                            class="grid grid-cols-[4rem_1fr_auto] items-center gap-3 rounded-xl border border-stone-200 bg-white p-3"
                        >
                            @if ($item['image'] ?? $item['imageUrl'] ?? null)
                                <img
                                    src="{{ $item['image'] ?? $item['imageUrl'] }}"
                                    alt="{{ $item['imageAlt'] ?? $item['title'] ?? '' }}"
                                    width="160"
                                    height="160"
                                    loading="lazy"
                                    decoding="async"
                                    class="aspect-square rounded-lg object-cover"
                                />
                            @else
                                <span
                                    class="aspect-square rounded-lg bg-[#17211c]/10"
                                    aria-hidden="true"
                                ></span>
                            @endif
                            <div>
                                <h3 class="text-sm font-black text-[#17211c]">
                                    {{ $item['title'] ?? $item['label'] ?? '' }}
                                </h3>
                                <p
                                    class="mt-1 text-xs font-bold text-stone-500"
                                >
                                    {{ __('capell-theme-commerce::generic.basket_items_label') }}:
                                    {{ $item['quantity'] ?? 1 }}
                                </p>
                            </div>
                            <p class="text-sm font-black text-[#1f5f4a]">
                                {{ $item['price'] ?? $item['total'] ?? '' }}
                            </p>
                        </article>
                    @empty
                        <article
                            class="rounded-xl border border-dashed border-stone-300 p-4"
                        >
                            <h3 class="text-sm font-black text-[#17211c]">
                                {{ __('capell-theme-commerce::generic.basket_empty_title') }}
                            </h3>
                            <p class="mt-2 text-sm text-stone-600">
                                {{ __('capell-theme-commerce::generic.basket_empty_summary') }}
                            </p>
                        </article>
                    @endforelse
                </div>

                <div class="mt-5 border-t border-stone-200 pt-5">
                    <div class="flex items-center justify-between gap-4">
                        <p class="text-sm font-black text-stone-600">
                            {{ __('capell-theme-commerce::generic.basket_subtotal_label') }}
                        </p>
                        <p class="text-xl font-black text-[#17211c]">
                            {{ $subtotal ?? '-' }}
                        </p>
                    </div>
                    @if ($deliveryNote)
                        <p class="mt-2 text-sm font-bold text-stone-500">
                            {{ __('capell-theme-commerce::generic.basket_delivery_label') }}:
                            {{ $deliveryNote }}
                        </p>
                    @endif

                    <a
                        href="{{ $checkoutUrl }}"
                        class="mt-5 inline-flex w-full justify-center rounded-full bg-[#e86f5c] px-6 py-3 text-sm font-black text-white"
                    >
                        {{ $checkoutLabel }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
