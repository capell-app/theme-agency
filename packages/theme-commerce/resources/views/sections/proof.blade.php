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
                    <figure
                        class="{{ $loop->first ? 'sm:col-span-2' : '' }} group overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] transition hover:border-[#e86f5c]/60 hover:bg-white/[0.08]"
                    >
                        <div
                            class="grid min-h-full md:grid-cols-[0.85fr_1.15fr]"
                        >
                            <div
                                class="flex min-h-36 items-end bg-[#101a16] p-5"
                                aria-hidden="true"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="h-2 w-16 rounded-full bg-[#e86f5c]"
                                        ></span>
                                        <span
                                            class="h-2 w-10 rounded-full bg-[#1f5f4a]"
                                        ></span>
                                    </div>
                                    <div class="mt-8 space-y-2">
                                        <span
                                            class="block h-2 rounded-full bg-white/45"
                                        ></span>
                                        <span
                                            class="block h-2 w-2/3 rounded-full bg-white/25"
                                        ></span>
                                    </div>
                                    <div class="mt-5 grid grid-cols-3 gap-2">
                                        <span
                                            class="h-8 rounded-md bg-[#e86f5c]/35"
                                        ></span>
                                        <span
                                            class="h-8 rounded-md bg-[#1f5f4a]/60"
                                        ></span>
                                        <span
                                            class="h-8 rounded-md bg-white/10"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <figcaption class="p-6">
                                <blockquote
                                    class="text-3xl leading-tight font-black text-white"
                                >
                                    {{ $item['metric'] ?? $item['quote'] ?? '' }}
                                </blockquote>
                                <p
                                    class="mt-3 text-sm leading-6 text-stone-200"
                                >
                                    {{ $item['excerpt'] ?? $item['summary'] ?? '' }}
                                </p>
                                <p
                                    class="mt-5 border-t border-white/10 pt-4 text-xs font-black tracking-wide text-[#e86f5c] uppercase"
                                >
                                    {{ $item['name'] ?? $item['logo'] ?? __('capell-theme-commerce::generic.merchandising_signal') }}
                                </p>
                                @if ($item['role'] ?? null)
                                    <p class="mt-1 text-xs text-stone-400">
                                        {{ $item['role'] }}
                                    </p>
                                @endif
                            </figcaption>
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
