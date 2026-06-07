@php
    $heading ??= $section->heading ?? null;
    $summary ??= $section->summary ?? null;
    $caseItems = $items ?? $section->items ?? [];
@endphp

<section
    id="case-studies"
    class="theme-section theme-section-case-studies portfolio-bg-deep text-white"
>
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-8 lg:grid-cols-[0.76fr_1fr] lg:items-end">
            <div>
                <p
                    class="portfolio-text-highlight text-xs font-black uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.case_studies_label') }}
                </p>
                @isset($heading)
                    <h2
                        class="mt-4 max-w-3xl text-4xl font-black tracking-tight"
                    >
                        {{ $heading }}
                    </h2>
                @endisset
            </div>

            <div class="grid gap-4">
                <p class="max-w-2xl text-base leading-7 text-slate-300">
                    {{ $summary ?? ($contentSectionsAvailable ?? false ? __('capell-theme-portfolio::generic.case_studies_connected') : __('capell-theme-portfolio::generic.case_studies_static')) }}
                </p>
                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="border border-white/10 bg-white/[0.06] p-4">
                        <p
                            class="portfolio-text-highlight text-xs font-black uppercase"
                        >
                            {{ __('capell-theme-portfolio::generic.case_scope_label') }}
                        </p>
                        <p class="mt-2 text-sm font-black text-white">
                            Strategy to launch
                        </p>
                    </div>
                    <div class="border border-white/10 bg-white/[0.06] p-4">
                        <p
                            class="portfolio-text-highlight text-xs font-black uppercase"
                        >
                            {{ __('capell-theme-portfolio::generic.case_artifacts_label') }}
                        </p>
                        <p class="mt-2 text-sm font-black text-white">
                            Deck, page, story
                        </p>
                    </div>
                    <div class="border border-white/10 bg-white/[0.06] p-4">
                        <p
                            class="portfolio-text-highlight text-xs font-black uppercase"
                        >
                            {{ __('capell-theme-portfolio::generic.case_role_label') }}
                        </p>
                        <p class="mt-2 text-sm font-black text-white">
                            Studio-led
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="theme-carousel relative mt-10"
            data-carousel="portfolio-case-studies"
        >
            <div
                class="flex snap-x snap-mandatory [scrollbar-width:none] gap-4 overflow-x-auto pr-4 pb-2 [&::-webkit-scrollbar]:hidden"
                data-carousel-track
            >
                @forelse ($caseItems as $item)
                    <article
                        class="grid min-w-[82%] snap-start overflow-hidden border border-white/10 bg-white/[0.06] shadow-2xl shadow-black/20 md:min-w-[560px] lg:min-w-[640px] lg:grid-cols-[0.72fr_1fr]"
                    >
                        <div class="portfolio-bg-deep-soft p-5">
                            <div
                                class="portfolio-bg-deep flex aspect-[4/3] flex-col justify-between border border-white/10 p-5"
                            >
                                <div
                                    class="flex items-start justify-between gap-4"
                                >
                                    <p
                                        class="portfolio-text-highlight text-xs font-black uppercase"
                                    >
                                        {{ __('capell-theme-portfolio::generic.case_slide_label') }}
                                    </p>
                                    <p
                                        class="font-mono text-sm font-black text-white/45"
                                    >
                                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </p>
                                </div>

                                <div
                                    class="space-y-3"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="portfolio-bg-highlight block h-2 w-28"
                                    ></span>
                                    <span
                                        class="block h-3 w-3/4 bg-white/35"
                                    ></span>
                                    <span
                                        class="block h-3 w-1/2 bg-white/20"
                                    ></span>
                                    <span class="mt-5 grid grid-cols-3 gap-3">
                                        <span class="h-12 bg-white/15"></span>
                                        <span
                                            class="portfolio-bg-secondary h-12"
                                        ></span>
                                        <span class="h-12 bg-white/10"></span>
                                    </span>
                                </div>

                                <div
                                    class="grid grid-cols-3 gap-2 text-[0.65rem] font-black text-white/65 uppercase"
                                >
                                    <span>
                                        {{ __('capell-theme-portfolio::generic.brief_label') }}
                                    </span>
                                    <span>
                                        {{ __('capell-theme-portfolio::generic.build_label') }}
                                    </span>
                                    <span>
                                        {{ __('capell-theme-portfolio::generic.publish_label') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="grid content-between gap-6 p-5 sm:p-6">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span
                                        class="portfolio-bg-warm portfolio-text-primary-strong px-3 py-1 text-xs font-black uppercase"
                                    >
                                        {{ $item['type'] ?? __('capell-theme-portfolio::generic.featured_heading') }}
                                    </span>
                                    <span
                                        class="portfolio-bg-secondary px-3 py-1 text-xs font-black text-white uppercase"
                                    >
                                        {{ __('capell-theme-portfolio::generic.outcome_label') }}
                                        {{ $item['metric'] ?? __('capell-theme-portfolio::generic.outcome_metric') }}
                                    </span>
                                </div>

                                <h3 class="mt-5 text-2xl font-black text-white">
                                    {{ $item['title'] ?? $item['name'] ?? '' }}
                                </h3>
                                <p
                                    class="mt-3 text-sm leading-7 text-slate-300"
                                >
                                    {{ $item['summary'] ?? $item['description'] ?? __('capell-theme-portfolio::generic.case_method_summary') }}
                                </p>
                            </div>

                            <div
                                class="grid gap-3 border-t border-white/10 pt-5 sm:grid-cols-3"
                            >
                                <div>
                                    <p
                                        class="portfolio-text-highlight text-xs font-black uppercase"
                                    >
                                        {{ __('capell-theme-portfolio::generic.case_scope_label') }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-bold text-slate-200"
                                    >
                                        {{ $item['scope'] ?? __('capell-theme-portfolio::generic.case_method_label') }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="portfolio-text-highlight text-xs font-black uppercase"
                                    >
                                        {{ __('capell-theme-portfolio::generic.case_role_label') }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-bold text-slate-200"
                                    >
                                        {{ $item['role'] ?? 'Creative lead' }}
                                    </p>
                                </div>
                                <div>
                                    <p
                                        class="portfolio-text-highlight text-xs font-black uppercase"
                                    >
                                        {{ __('capell-theme-portfolio::generic.case_timeline_label') }}
                                    </p>
                                    <p
                                        class="mt-2 text-sm font-bold text-slate-200"
                                    >
                                        {{ $item['timeline'] ?? '4-8 weeks' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <article
                        class="min-w-[82%] snap-start border border-dashed border-white/20 bg-white/[0.06] p-6 md:min-w-[560px] lg:min-w-[640px]"
                    >
                        <h3 class="text-lg font-black text-white">
                            {{ __('capell-theme-portfolio::generic.premium_layout_ready') }}
                        </h3>
                        <p class="mt-2 text-sm text-slate-300">
                            {{ __('capell-theme-portfolio::generic.premium_layout_empty') }}
                        </p>
                    </article>
                @endforelse
            </div>

            <div class="mt-5 flex gap-3">
                <button
                    type="button"
                    class="theme-carousel-button border border-white/20 bg-white/10 px-4 py-2 text-xs font-black text-white uppercase"
                    aria-label="{{ __('capell-theme-portfolio::generic.carousel_previous') }}"
                    data-carousel-prev
                >
                    Prev
                </button>
                <button
                    type="button"
                    class="theme-carousel-button border border-white/20 bg-white/10 px-4 py-2 text-xs font-black text-white uppercase"
                    aria-label="{{ __('capell-theme-portfolio::generic.carousel_next') }}"
                    data-carousel-next
                >
                    Next
                </button>
            </div>
        </div>
    </div>
</section>
