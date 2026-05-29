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
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? '');
@endphp

<section class="theme-section theme-section-hero bg-[#f8fafc] px-6 py-20">
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-8 lg:grid-cols-[1fr_0.95fr] lg:items-end"
        >
            <div class="space-y-5">
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#1f3173] uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h2
                    class="max-w-3xl text-5xl font-black tracking-normal text-[#0f172a]"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="max-w-2xl text-lg leading-8 text-stone-600">
                        {{ $summary }}
                    </p>
                @endif

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#case-studies' }}"
                        class="inline-flex rounded-full bg-[#0f172a] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-portfolio::generic.view_case_label') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#contact' }}"
                        class="inline-flex rounded-full border border-[#cbd5e1] px-5 py-3 text-sm font-black text-[#0f172a]"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-portfolio::generic.media_kit_label') }}
                    </a>
                </div>
            </div>

            <div
                class="border border-[#d6dce8] bg-white p-3 shadow-2xl shadow-slate-950/10"
            >
                @if ($imageUrl)
                    <img
                        src="{{ $imageUrl }}"
                        alt="{{ $imageAlt }}"
                        class="aspect-[4/3] w-full object-cover"
                    />
                @else
                    <div
                        class="flex aspect-[4/3] items-end bg-[#070b1a] p-5 text-white"
                        aria-hidden="true"
                    >
                        <div class="w-full">
                            <div class="flex items-center justify-between">
                                <span
                                    class="h-2 w-20 rounded-full bg-[#fb923c]"
                                ></span>
                                <span
                                    class="text-[0.65rem] font-black tracking-[0.18em] text-white/50 uppercase"
                                >
                                    01
                                </span>
                            </div>
                            <div class="mt-16 space-y-3">
                                <span
                                    class="block h-3 w-3/4 rounded-full bg-white/45"
                                ></span>
                                <span
                                    class="block h-3 w-1/2 rounded-full bg-white/25"
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

                <div class="grid border-t border-[#e2e8f0] sm:grid-cols-3">
                    <div class="p-4">
                        <p
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                        >
                            {{ __('capell-theme-portfolio::generic.hero_signal_label') }}
                        </p>
                        <p class="mt-2 text-2xl font-black">+42%</p>
                    </div>
                    <div
                        class="border-t border-[#e2e8f0] p-4 sm:border-t-0 sm:border-l"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                        >
                            {{ __('capell-theme-portfolio::generic.hero_stories_label') }}
                        </p>
                        <p class="mt-2 text-2xl font-black">120+</p>
                    </div>
                    <div
                        class="border-t border-[#e2e8f0] p-4 sm:border-t-0 sm:border-l"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                        >
                            {{ __('capell-theme-portfolio::generic.hero_launch_label') }}
                        </p>
                        <p class="mt-2 text-2xl font-black">6h</p>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
