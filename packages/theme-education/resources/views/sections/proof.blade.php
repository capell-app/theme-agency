@php
    $items = $section->items ?? [];
@endphp

<section class="theme-section theme-section-proof bg-[#050b24] text-white">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[0.66fr_1.34fr] lg:items-start">
            <div>
                <p class="text-xs font-black text-[#5eead4] uppercase">
                    {{ __('capell-theme-education::generic.proof_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
                @if ($section->summary ?? null)
                    <p class="mt-4 max-w-md text-base leading-7 text-slate-300">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                @foreach ($items as $item)
                    <article
                        class="{{ $loop->first ? 'md:col-span-2' : '' }} border border-white/10 bg-white/[0.06]"
                    >
                        <div
                            class="{{ $loop->first ? 'md:grid-cols-[0.46fr_1fr_0.42fr]' : 'md:grid-cols-[0.7fr_1fr]' }} grid"
                        >
                            <div class="bg-[#0f1b3d] p-5">
                                <p
                                    class="text-xs font-black text-[#5eead4] uppercase"
                                >
                                    {{ __('capell-theme-education::generic.cohort_metric_label') }}
                                </p>
                                <p class="mt-5 font-mono text-5xl font-black">
                                    {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <div class="p-5">
                                <p
                                    class="text-xs font-black text-[#93c5fd] uppercase"
                                >
                                    {{ $item['name'] ?? $item['title'] ?? __('capell-theme-education::generic.course_signal') }}
                                </p>
                                <p
                                    class="mt-4 text-sm leading-7 text-slate-200"
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
                                            class="block h-2 w-20 bg-[#5eead4]"
                                        ></span>
                                        <span
                                            class="block h-2 w-28 bg-white/25"
                                        ></span>
                                        <span
                                            class="block h-2 w-16 bg-[#4338ca]"
                                        ></span>
                                    </div>
                                    <div class="mt-8 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/15"></span>
                                        <span class="h-9 bg-[#4338ca]"></span>
                                        <span
                                            class="h-9 bg-[#14b8a6]/50"
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
