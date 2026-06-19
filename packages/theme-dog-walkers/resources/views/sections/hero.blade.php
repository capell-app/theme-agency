@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-dog-walkers::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-dog-walkers::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-dog-walkers::generic.hero_primary_action'),
        'url' => '#enquiry',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-dog-walkers::generic.hero_secondary_action'),
        'url' => '#areas',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt
        ?? ($section->imageAlt ?? ($imageAlt ?? ($heading ?? __('capell-theme-dog-walkers::generic.hero_image_alt'))));
    $imageWidth = (int) ($section->mediaWidth ?? ($section->imageWidth ?? ($imageWidth ?? 1600)));
    $imageHeight = (int) ($section->mediaHeight ?? ($section->imageHeight ?? ($imageHeight ?? 1000)));
@endphp

<section class="theme-section theme-section-hero overflow-hidden bg-[#ecfdf5]">
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:py-20"
        >
            <div>
                <p
                    class="text-xs font-semibold tracking-[0.12em] text-[#f97316] uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h2
                    class="mt-5 max-w-3xl text-4xl leading-tight font-extrabold tracking-normal text-[#071b17] sm:text-5xl"
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
                        href="{{ $primaryAction['url'] ?? '#enquiry' }}"
                        class="inline-flex bg-[#0f766e] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-dog-walkers::generic.hero_primary_action') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#areas' }}"
                        class="inline-flex border border-[#99f6e4] bg-white px-5 py-3 text-sm font-black text-[#115e59]"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-dog-walkers::generic.hero_secondary_action') }}
                    </a>
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-3">
                    <div class="border border-[#99f6e4] bg-white p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[#0f766e] uppercase"
                        >
                            {{ __('capell-theme-dog-walkers::generic.hero_metric_response_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-[#071b17]">
                            {{ __('capell-theme-dog-walkers::generic.hero_metric_response_value') }}
                        </p>
                    </div>
                    <div class="border border-[#99f6e4] bg-white p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[#0f766e] uppercase"
                        >
                            {{ __('capell-theme-dog-walkers::generic.hero_metric_coverage_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-[#071b17]">
                            {{ __('capell-theme-dog-walkers::generic.hero_metric_coverage_value') }}
                        </p>
                    </div>
                    <div class="border border-[#fed7aa] bg-[#fff7ed] p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[#c2410c] uppercase"
                        >
                            {{ __('capell-theme-dog-walkers::generic.hero_metric_slots_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-[#071b17]">
                            {{ __('capell-theme-dog-walkers::generic.hero_metric_slots_value') }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="border border-[#99f6e4] bg-white p-3 shadow-2xl shadow-teal-950/10"
            >
                <div class="grid gap-3 lg:grid-cols-[1fr_0.72fr]">
                    <div class="bg-[#042f2e] p-4 text-white">
                        @if ($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $imageAlt }}"
                                width="{{ $imageWidth }}"
                                height="{{ $imageHeight }}"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                                sizes="(min-width: 1024px) 50vw, 100vw"
                                class="aspect-[16/10] w-full object-cover"
                            />
                        @else
                            <div
                                class="grid aspect-[16/10] bg-[#0f766e] p-4"
                                aria-hidden="true"
                            >
                                <div class="grid grid-cols-3 gap-3">
                                    <span class="h-20 bg-white/20"></span>
                                    <span class="h-20 bg-white/10"></span>
                                    <span class="h-20 bg-white/20"></span>
                                </div>
                                <div class="mt-5 grid gap-2">
                                    <span class="h-3 w-3/4 bg-white/50"></span>
                                    <span class="h-3 w-1/2 bg-white/25"></span>
                                </div>
                            </div>
                        @endif

                        <div class="mt-4 grid gap-3 sm:grid-cols-3">
                            <span
                                class="bg-white/10 px-3 py-2 text-xs font-black tracking-[0.14em] text-teal-50 uppercase"
                            >
                                {{ __('capell-theme-dog-walkers::generic.cta_step_scope') }}
                            </span>
                            <span
                                class="bg-white/10 px-3 py-2 text-xs font-black tracking-[0.14em] text-teal-50 uppercase"
                            >
                                {{ __('capell-theme-dog-walkers::generic.cta_step_slot') }}
                            </span>
                            <span
                                class="bg-white/10 px-3 py-2 text-xs font-black tracking-[0.14em] text-teal-50 uppercase"
                            >
                                {{ __('capell-theme-dog-walkers::generic.cta_step_confirm') }}
                            </span>
                        </div>
                    </div>

                    <div class="grid gap-3">
                        <div class="border border-[#ccfbf1] bg-[#f0fdfa] p-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                            >
                                {{ __('capell-theme-dog-walkers::generic.hero_route_label') }}
                            </p>
                            <div class="mt-4 space-y-3">
                                <div class="grid grid-cols-[1fr_auto] gap-3">
                                    <span class="h-3 bg-[#115e59]"></span>
                                    <span class="h-3 w-10 bg-[#f97316]"></span>
                                </div>
                                <div class="grid grid-cols-[0.7fr_1fr] gap-3">
                                    <span
                                        class="h-10 border border-[#99f6e4] bg-white"
                                    ></span>
                                    <span
                                        class="h-10 border border-[#99f6e4] bg-white"
                                    ></span>
                                </div>
                                <div class="grid grid-cols-[1fr_0.6fr] gap-3">
                                    <span
                                        class="h-10 border border-[#99f6e4] bg-white"
                                    ></span>
                                    <span
                                        class="h-10 border border-[#99f6e4] bg-white"
                                    ></span>
                                </div>
                            </div>
                        </div>

                        <div class="border border-[#fed7aa] bg-[#fff7ed] p-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#c2410c] uppercase"
                            >
                                {{ __('capell-theme-dog-walkers::generic.hero_enquiry_label') }}
                            </p>
                            <p class="mt-2 text-2xl font-black text-[#071b17]">
                                {{ __('capell-theme-dog-walkers::generic.hero_enquiry_value') }}
                            </p>
                            <p class="mt-2 text-sm font-bold text-slate-600">
                                {{ __('capell-theme-dog-walkers::generic.enquiry_eta') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
