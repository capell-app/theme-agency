@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-portfolio::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-portfolio::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-portfolio::generic.view_case_label'),
        'url' => '#case-studies',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-portfolio::generic.media_kit_label'),
        'url' => '#contact',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? ($heading ?? __('capell-theme-portfolio::generic.hero_image_alt')));
@endphp

<section
    class="theme-section theme-section-hero portfolio-bg-surface-cool px-6 py-16 lg:py-20"
>
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-center"
        >
            <div>
                <p
                    class="portfolio-text-secondary text-xs font-black uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h2
                    class="portfolio-text-ink mt-5 max-w-3xl text-5xl font-black tracking-normal lg:text-6xl"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="mt-5 max-w-2xl text-lg leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif

                <div class="mt-7 flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#case-studies' }}"
                        class="portfolio-bg-ink inline-flex px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-portfolio::generic.view_case_label') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#contact' }}"
                        class="portfolio-border-strong portfolio-text-ink inline-flex border bg-white px-5 py-3 text-sm font-black"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-portfolio::generic.media_kit_label') }}
                    </a>
                </div>

                <dl
                    class="portfolio-border-strong mt-8 grid max-w-xl grid-cols-3 border bg-white"
                >
                    <div class="p-4">
                        <dt class="text-xs font-black text-slate-500 uppercase">
                            {{ __('capell-theme-portfolio::generic.hero_signal_label') }}
                        </dt>
                        <dd class="portfolio-text-ink mt-2 text-2xl font-black">
                            {{ __('capell-theme-portfolio::generic.hero_signal_value') }}
                        </dd>
                    </div>
                    <div class="portfolio-border-strong border-l p-4">
                        <dt class="text-xs font-black text-slate-500 uppercase">
                            {{ __('capell-theme-portfolio::generic.hero_stories_label') }}
                        </dt>
                        <dd class="portfolio-text-ink mt-2 text-2xl font-black">
                            {{ __('capell-theme-portfolio::generic.hero_stories_value') }}
                        </dd>
                    </div>
                    <div class="portfolio-border-strong border-l p-4">
                        <dt class="text-xs font-black text-slate-500 uppercase">
                            {{ __('capell-theme-portfolio::generic.hero_launch_label') }}
                        </dt>
                        <dd class="portfolio-text-ink mt-2 text-2xl font-black">
                            {{ __('capell-theme-portfolio::generic.hero_launch_value') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div
                class="portfolio-border-strong border bg-white p-3 shadow-2xl shadow-slate-950/10"
            >
                <div class="grid gap-3 lg:grid-cols-[1fr_0.48fr]">
                    <div class="portfolio-bg-deep min-h-full p-4 text-white">
                        @if ($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $imageAlt }}"
                                width="960"
                                height="720"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                                class="aspect-[4/3] w-full object-cover"
                            />
                        @else
                            <div
                                class="flex aspect-[4/3] items-end p-5"
                                aria-hidden="true"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between"
                                    >
                                        <span
                                            class="portfolio-bg-highlight h-2 w-20"
                                        ></span>
                                        <span
                                            class="text-[0.65rem] font-black text-white/50 uppercase"
                                        >
                                            01
                                        </span>
                                    </div>
                                    <div class="mt-16 space-y-3">
                                        <span
                                            class="block h-3 w-3/4 bg-white/45"
                                        ></span>
                                        <span
                                            class="block h-3 w-1/2 bg-white/25"
                                        ></span>
                                    </div>
                                    <div class="mt-6 grid grid-cols-3 gap-3">
                                        <span class="h-10 bg-white/20"></span>
                                        <span
                                            class="portfolio-bg-secondary h-10"
                                        ></span>
                                        <span class="h-10 bg-white/10"></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-3">
                        <div
                            class="portfolio-border-strong portfolio-bg-card-soft border p-4"
                        >
                            <p
                                class="portfolio-text-secondary text-xs font-black uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.case_file_label') }}
                            </p>
                            <p
                                class="portfolio-text-ink mt-3 text-lg font-black"
                            >
                                {{ __('capell-theme-portfolio::generic.case_file_summary') }}
                            </p>
                        </div>
                        <div
                            class="portfolio-border-strong portfolio-bg-warm border p-4"
                        >
                            <p
                                class="portfolio-text-primary-strong text-xs font-black uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.outcome_label') }}
                            </p>
                            <p
                                class="portfolio-text-ink mt-3 text-lg font-black"
                            >
                                {{ __('capell-theme-portfolio::generic.outcome_summary') }}
                            </p>
                        </div>
                        <div
                            class="portfolio-border-strong border bg-white p-4"
                        >
                            <p
                                class="text-xs font-black text-slate-500 uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.media_kit_label') }}
                            </p>
                            <div
                                class="mt-4 grid grid-cols-3 gap-2"
                                aria-hidden="true"
                            >
                                <span class="portfolio-bg-deep h-8"></span>
                                <span class="portfolio-bg-secondary h-8"></span>
                                <span class="portfolio-bg-highlight h-8"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
