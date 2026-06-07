@php
    $gallery = $section->gallery ?? $section->media ?? [];
    $variants = $section->variants ?? $section->options ?? [];
    $recommendations = $section->recommendations ?? $section->related ?? [];
    $stockStatus = $section->stockStatus ?? $section->stock ?? null;
    $ctaLabel = $section->ctaLabel ?? __('capell-theme-commerce::generic.product_add_to_basket');
    $ctaUrl = $section->ctaUrl ?? $section->url ?? '#';
    $trustItems = $section->trustItems ?? [
        __('capell-theme-commerce::generic.product_shipping_label'),
        __('capell-theme-commerce::generic.product_returns_label'),
        __('capell-theme-commerce::generic.product_secure_checkout_label'),
    ];
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
                    @if (($gallery[0]['url'] ?? $gallery[0]['image'] ?? null) || ($section->image ?? $section->imageUrl ?? null))
                        <img
                            src="{{ $gallery[0]['url'] ?? $gallery[0]['image'] ?? $section->image ?? $section->imageUrl }}"
                            alt="{{ $gallery[0]['alt'] ?? $gallery[0]['imageAlt'] ?? $section->imageAlt ?? $section->heading ?? '' }}"
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
            @if ($section->heading ?? null)
                <h2
                    class="text-4xl font-black tracking-tight text-[var(--retail-ink)]"
                >
                    {{ $section->heading }}
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

            @if ($section->summary ?? null)
                <p class="mt-4 text-lg text-stone-600">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-6 flex flex-wrap items-end gap-3">
                @if ($section->price ?? null)
                    <p class="text-3xl font-black text-[var(--retail-ink)]">
                        {{ $section->price }}
                    </p>
                @endif

                @if ($section->compareAtPrice ?? $section->compareAt ?? null)
                    <p class="text-sm font-bold text-stone-500 line-through">
                        <span class="sr-only">
                            {{ __('capell-theme-commerce::generic.product_compare_at_label') }}
                        </span>
                        {{ $section->compareAtPrice ?? $section->compareAt }}
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
                                {{ $variant['label'] ?? $variant['name'] ?? $variant }}
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
