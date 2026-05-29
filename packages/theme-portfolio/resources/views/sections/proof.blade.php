@php
    $items = $section->items ?? [];
@endphp

<section class="theme-section theme-section-proof bg-[#070b1a] text-white">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#fb923c] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.evidence_label') }}
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

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($items as $item)
                    <figure
                        class="{{ $loop->first ? 'sm:col-span-2' : '' }} group overflow-hidden border border-white/10 bg-white/[0.05] shadow-2xl shadow-black/20"
                    >
                        <div class="grid gap-0 md:grid-cols-[0.9fr_1.1fr]">
                            <div
                                class="flex min-h-44 items-end bg-[#10162b] p-5"
                                aria-hidden="true"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="h-2 w-16 rounded-full bg-[#fb923c]"
                                        ></span>
                                        <span
                                            class="text-[0.65rem] font-black tracking-[0.18em] text-white/50 uppercase"
                                        >
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <div class="mt-8 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/20"></span>
                                        <span class="h-9 bg-[#1f3173]"></span>
                                        <span class="h-9 bg-white/10"></span>
                                    </div>
                                </div>
                            </div>

                            <figcaption class="p-5 sm:p-6">
                                <p
                                    class="font-mono text-4xl font-black text-white"
                                >
                                    {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                                <blockquote
                                    class="mt-4 text-sm leading-7 text-slate-200"
                                >
                                    {{ $item['quote'] ?? $item['summary'] ?? '' }}
                                </blockquote>
                                <p
                                    class="mt-5 border-t border-white/10 pt-4 text-xs font-black tracking-[0.14em] text-[#fb923c] uppercase"
                                >
                                    {{ $item['title'] ?? $item['name'] ?? $item['logo'] ?? __('capell-theme-portfolio::generic.proof_signal') }}
                                </p>
                            </figcaption>
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
