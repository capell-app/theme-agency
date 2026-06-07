<section class="retail-proof border-y border-stone-200 bg-[#17211c] text-white">
    <div class="px-6">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#e86f5c] uppercase"
                >
                    {{ __('capell-theme-commerce::generic.retail_proof_label') }}
                </p>
                <h2
                    class="mt-4 max-w-xl text-4xl font-black tracking-tight text-white"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-md text-stone-300">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach (($section->items ?? []) as $item)
                    <article
                        class="{{ $loop->first ? 'sm:col-span-2' : '' }} overflow-hidden rounded-2xl border border-white/10 bg-[#f8eee3] text-[#17211c] shadow-[0_18px_60px_rgba(0,0,0,0.24)]"
                    >
                        <div
                            class="{{ $loop->first ? 'md:grid-cols-[0.72fr_1fr_0.58fr]' : 'md:grid-cols-[0.72fr_1fr]' }} grid min-h-full"
                        >
                            <div
                                class="flex min-h-36 items-end bg-[#101a16] p-5 text-white"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="text-xs font-black text-[#f6e6d7] uppercase"
                                        >
                                            {{ __('capell-theme-commerce::generic.proof_order_label') }}
                                        </span>
                                        <span
                                            class="rounded-full bg-[#e86f5c] px-2 py-1 text-xs font-black text-white"
                                        >
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <div class="mt-8 grid grid-cols-3 gap-2">
                                        <span
                                            class="h-16 rounded-md bg-[#f8eee3]/80"
                                        ></span>
                                        <span
                                            class="h-16 rounded-md bg-[#1f5f4a]"
                                        ></span>
                                        <span
                                            class="h-16 rounded-md bg-[#e86f5c]/80"
                                        ></span>
                                    </div>
                                    <div
                                        class="mt-4 h-2 rounded-full bg-white/15"
                                    >
                                        <span
                                            class="block h-2 w-3/4 rounded-full bg-[#e86f5c]"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-6">
                                <p
                                    class="text-xs font-black text-[#1f5f4a] uppercase"
                                >
                                    {{ $item['name'] ?? $item['logo'] ?? __('capell-theme-commerce::generic.merchandising_signal') }}
                                </p>
                                <p
                                    class="mt-4 text-5xl leading-none font-black text-[#17211c]"
                                >
                                    {{ $item['metric'] ?? $item['quote'] ?? '' }}
                                </p>
                                <p
                                    class="mt-4 text-sm leading-6 text-stone-700"
                                >
                                    {{ $item['excerpt'] ?? $item['summary'] ?? '' }}
                                </p>
                                @if (($item['rating'] ?? $item['stars'] ?? null) || ($item['reviewCount'] ?? $item['reviews'] ?? null))
                                    <div
                                        class="mt-4 flex flex-wrap items-center gap-2 text-sm font-black text-[#1f5f4a]"
                                        aria-label="{{ __('capell-theme-commerce::generic.rating_label') }}"
                                    >
                                        @if ($item['rating'] ?? $item['stars'] ?? null)
                                            <span>
                                                {{ ($item['rating'] ?? $item['stars']) . ' / 5' }}
                                            </span>
                                        @endif

                                        @if ($item['reviewCount'] ?? $item['reviews'] ?? null)
                                            <span class="text-stone-500">
                                                {{ $item['reviewCount'] ?? $item['reviews'] }}
                                                {{ __('capell-theme-commerce::generic.reviews_label') }}
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                @if ($item['role'] ?? null)
                                    <p
                                        class="mt-3 text-xs font-semibold text-stone-500"
                                    >
                                        {{ $item['role'] }}
                                    </p>
                                @endif
                            </div>

                            @if ($loop->first)
                                <div
                                    class="border-t border-[#e8ddd0] bg-white/65 p-5 md:border-t-0 md:border-l"
                                >
                                    <div class="space-y-4">
                                        <div>
                                            <p
                                                class="text-xs font-black text-stone-500 uppercase"
                                            >
                                                {{ __('capell-theme-commerce::generic.proof_channel_label') }}
                                            </p>
                                            <div class="mt-2 flex gap-2">
                                                <span
                                                    class="h-8 flex-1 rounded-md bg-[#17211c]"
                                                ></span>
                                                <span
                                                    class="h-8 flex-1 rounded-md bg-[#1f5f4a]"
                                                ></span>
                                            </div>
                                        </div>
                                        <div>
                                            <p
                                                class="text-xs font-black text-stone-500 uppercase"
                                            >
                                                {{ __('capell-theme-commerce::generic.proof_stock_label') }}
                                            </p>
                                            <div
                                                class="mt-2 grid grid-cols-4 gap-2"
                                            >
                                                <span
                                                    class="h-9 rounded-md bg-[#e86f5c]"
                                                ></span>
                                                <span
                                                    class="h-9 rounded-md bg-[#f6e6d7]"
                                                ></span>
                                                <span
                                                    class="h-9 rounded-md bg-[#1f5f4a]"
                                                ></span>
                                                <span
                                                    class="h-9 rounded-md bg-[#17211c]"
                                                ></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
