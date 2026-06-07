@php
    $items = $section->items ?? [];
@endphp

<section class="theme-section theme-section-proof portfolio-bg-deep text-white">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-10 lg:grid-cols-[0.64fr_1.36fr] lg:items-start">
            <div>
                <p
                    class="portfolio-text-highlight text-xs font-black uppercase"
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

            <div class="grid gap-4 md:grid-cols-4">
                @foreach ($items as $item)
                    <figure
                        class="{{ $loop->first ? 'md:col-span-4' : 'md:col-span-2' }} overflow-hidden border border-white/10 bg-white/[0.05]"
                    >
                        <div
                            class="{{ $loop->first ? 'md:grid-cols-[0.48fr_1fr_0.42fr]' : 'md:grid-cols-[0.72fr_1fr]' }} grid min-h-full"
                        >
                            <div class="portfolio-bg-deep-soft p-5">
                                <p
                                    class="portfolio-text-highlight text-xs font-black uppercase"
                                >
                                    {{ __('capell-theme-portfolio::generic.outcome_label') }}
                                </p>
                                <p
                                    class="mt-5 font-mono text-5xl font-black text-white"
                                >
                                    {{ $item['metric'] ?? str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>

                            <figcaption class="p-5 sm:p-6">
                                <p
                                    class="portfolio-text-highlight text-xs font-black uppercase"
                                >
                                    {{ $item['title'] ?? $item['name'] ?? $item['logo'] ?? __('capell-theme-portfolio::generic.proof_signal') }}
                                </p>
                                <blockquote
                                    class="mt-4 text-sm leading-7 text-slate-200"
                                >
                                    {{ $item['quote'] ?? $item['summary'] ?? '' }}
                                </blockquote>
                            </figcaption>

                            @if ($loop->first)
                                <div
                                    class="hidden border-l border-white/10 p-5 md:block"
                                    aria-hidden="true"
                                >
                                    <div class="space-y-3">
                                        <span
                                            class="portfolio-bg-highlight block h-2 w-20"
                                        ></span>
                                        <span
                                            class="block h-2 w-28 bg-white/25"
                                        ></span>
                                        <span
                                            class="portfolio-bg-secondary block h-2 w-16"
                                        ></span>
                                    </div>
                                    <div class="mt-8 grid grid-cols-3 gap-2">
                                        <span class="h-9 bg-white/20"></span>
                                        <span
                                            class="portfolio-bg-secondary h-9"
                                        ></span>
                                        <span class="h-9 bg-white/10"></span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
