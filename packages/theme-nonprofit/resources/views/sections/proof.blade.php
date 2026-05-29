@php
    $items = $section->items ?? [];
@endphp

<section class="theme-section theme-section-proof bg-[#06140c] text-white">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[0.64fr_1.36fr] lg:items-start">
            <div>
                <p class="text-xs font-black text-[#fde047] uppercase">
                    {{ __('capell-theme-nonprofit::generic.proof_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p
                        class="mt-4 max-w-md text-base leading-7 text-emerald-100"
                    >
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-4">
                @foreach ($items as $item)
                    <article
                        class="{{ $loop->first ? 'md:col-span-4' : 'md:col-span-2' }} border border-white/10 bg-white/[0.06]"
                    >
                        <div
                            class="{{ $loop->first ? 'md:grid-cols-[0.44fr_1fr_0.42fr]' : 'md:grid-cols-[0.72fr_1fr]' }} grid"
                        >
                            <div class="bg-[#12351f] p-5">
                                <p
                                    class="text-xs font-black text-[#fde047] uppercase"
                                >
                                    {{ __('capell-theme-nonprofit::generic.supporter_metric_label') }}
                                </p>
                                <p
                                    class="mt-5 font-mono text-5xl font-black text-white"
                                >
                                    {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <div class="p-5">
                                <p
                                    class="text-xs font-black text-[#fde047] uppercase"
                                >
                                    {{ $item['name'] ?? $item['title'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                                </p>
                                <p
                                    class="mt-4 text-sm leading-7 text-emerald-50"
                                >
                                    {{ $item['summary'] ?? $item['description'] ?? '' }}
                                </p>
                            </div>

                            @if ($loop->first)
                                <div
                                    class="hidden border-l border-white/10 p-5 md:block"
                                    aria-hidden="true"
                                >
                                    <div class="space-y-3">
                                        <span
                                            class="block h-2 w-20 bg-[#fde047]"
                                        ></span>
                                        <span
                                            class="block h-2 w-28 bg-white/25"
                                        ></span>
                                        <span
                                            class="block h-2 w-16 bg-[#16a34a]"
                                        ></span>
                                    </div>
                                    <div class="mt-8 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/15"></span>
                                        <span class="h-9 bg-[#facc15]"></span>
                                        <span
                                            class="h-9 bg-[#16a34a]/70"
                                        ></span>
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
