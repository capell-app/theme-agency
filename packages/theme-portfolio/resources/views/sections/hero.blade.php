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
    class="theme-section theme-section-hero bg-[#eef2f7] px-6 py-16 lg:py-20"
>
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.82fr_1.18fr] lg:items-center"
        >
            <div>
                <p class="text-xs font-black text-[#1f3173] uppercase">
                    {{ $eyebrow }}
                </p>
                <h2
                    class="mt-5 max-w-3xl text-5xl font-black tracking-normal text-[#0f172a] lg:text-6xl"
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
                        class="inline-flex bg-[#0f172a] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-portfolio::generic.view_case_label') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#contact' }}"
                        class="inline-flex border border-[#cbd5e1] bg-white px-5 py-3 text-sm font-black text-[#0f172a]"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-portfolio::generic.media_kit_label') }}
                    </a>
                </div>

                <dl
                    class="mt-8 grid max-w-xl grid-cols-3 border border-[#d6dce8] bg-white"
                >
                    <div class="p-4">
                        <dt class="text-xs font-black text-slate-500 uppercase">
                            {{ __('capell-theme-portfolio::generic.hero_signal_label') }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black text-[#0f172a]">
                            {{ __('capell-theme-portfolio::generic.hero_signal_value') }}
                        </dd>
                    </div>
                    <div class="border-l border-[#e2e8f0] p-4">
                        <dt class="text-xs font-black text-slate-500 uppercase">
                            {{ __('capell-theme-portfolio::generic.hero_stories_label') }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black text-[#0f172a]">
                            {{ __('capell-theme-portfolio::generic.hero_stories_value') }}
                        </dd>
                    </div>
                    <div class="border-l border-[#e2e8f0] p-4">
                        <dt class="text-xs font-black text-slate-500 uppercase">
                            {{ __('capell-theme-portfolio::generic.hero_launch_label') }}
                        </dt>
                        <dd class="mt-2 text-2xl font-black text-[#0f172a]">
                            {{ __('capell-theme-portfolio::generic.hero_launch_value') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div
                class="border border-[#d6dce8] bg-white p-3 shadow-2xl shadow-slate-950/10"
            >
                <div class="grid gap-3 lg:grid-cols-[1fr_0.48fr]">
                    <div class="min-h-full bg-[#070b1a] p-4 text-white">
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
                                            class="h-2 w-20 bg-[#fb923c]"
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
                                        <span class="h-10 bg-[#1f3173]"></span>
                                        <span class="h-10 bg-white/10"></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-3">
                        <div class="border border-[#e2e8f0] bg-[#f8fafc] p-4">
                            <p
                                class="text-xs font-black text-[#1f3173] uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.case_file_label') }}
                            </p>
                            <p class="mt-3 text-lg font-black text-[#0f172a]">
                                {{ __('capell-theme-portfolio::generic.case_file_summary') }}
                            </p>
                        </div>
                        <div class="border border-[#e2e8f0] bg-[#fff7ed] p-4">
                            <p
                                class="text-xs font-black text-[#9a3412] uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.outcome_label') }}
                            </p>
                            <p class="mt-3 text-lg font-black text-[#0f172a]">
                                {{ __('capell-theme-portfolio::generic.outcome_summary') }}
                            </p>
                        </div>
                        <div class="border border-[#e2e8f0] bg-white p-4">
                            <p
                                class="text-xs font-black text-slate-500 uppercase"
                            >
                                {{ __('capell-theme-portfolio::generic.media_kit_label') }}
                            </p>
                            <div
                                class="mt-4 grid grid-cols-3 gap-2"
                                aria-hidden="true"
                            >
                                <span class="h-8 bg-[#070b1a]"></span>
                                <span class="h-8 bg-[#1f3173]"></span>
                                <span class="h-8 bg-[#fb923c]"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
