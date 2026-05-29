@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-knowledge::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-knowledge::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-knowledge::generic.hero_primary_action'),
        'url' => '#library',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-knowledge::generic.hero_secondary_action'),
        'url' => '#digest',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? '');
@endphp

<section class="theme-section theme-section-hero bg-[#f8fbff]">
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.92fr_1.08fr] lg:items-center lg:py-20"
        >
            <div class="space-y-5">
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#2563eb] uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h2
                    class="max-w-3xl text-5xl leading-tight font-black tracking-normal text-[#111827]"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="max-w-2xl text-lg leading-8 text-slate-600">
                        {{ $summary }}
                    </p>
                @endif

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#library' }}"
                        class="inline-flex bg-[#1d4ed8] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-knowledge::generic.hero_primary_action') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#digest' }}"
                        class="inline-flex border border-[#bfdbfe] bg-white px-5 py-3 text-sm font-black text-[#1d4ed8]"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-knowledge::generic.hero_secondary_action') }}
                    </a>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="border border-[#dbeafe] bg-white p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[#2563eb] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.hero_metric_guides_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-[#111827]">
                            {{ __('capell-theme-knowledge::generic.hero_metric_guides_value') }}
                        </p>
                    </div>
                    <div class="border border-[#dbeafe] bg-white p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[#2563eb] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.hero_metric_topics_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-[#111827]">
                            {{ __('capell-theme-knowledge::generic.hero_metric_topics_value') }}
                        </p>
                    </div>
                    <div class="border border-[#fde68a] bg-[#fffbeb] p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[#b45309] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.hero_metric_saved_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-[#111827]">
                            {{ __('capell-theme-knowledge::generic.hero_metric_saved_value') }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="border border-[#bfdbfe] bg-white p-3 shadow-2xl shadow-blue-950/10"
            >
                <div class="border border-[#dbeafe] bg-[#eff6ff] p-4">
                    <div class="grid gap-4 lg:grid-cols-[1fr_0.75fr]">
                        <div class="bg-white p-4">
                            @if ($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $imageAlt }}"
                                    class="aspect-[16/10] w-full object-cover"
                                />
                            @else
                                <div
                                    class="aspect-[16/10] bg-[#172554] p-5"
                                    aria-hidden="true"
                                >
                                    <div class="grid h-full content-end gap-4">
                                        <span
                                            class="h-4 w-24 bg-[#f59e0b]"
                                        ></span>
                                        <span
                                            class="h-3 w-3/4 bg-white/60"
                                        ></span>
                                        <span
                                            class="h-3 w-1/2 bg-white/30"
                                        ></span>
                                        <div class="grid grid-cols-3 gap-3">
                                            <span
                                                class="h-12 bg-white/15"
                                            ></span>
                                            <span
                                                class="h-12 bg-[#1d4ed8]"
                                            ></span>
                                            <span
                                                class="h-12 bg-white/15"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <span
                                    class="border border-[#bfdbfe] bg-[#eff6ff] px-3 py-2 text-xs font-black tracking-[0.14em] text-[#1d4ed8] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.cta_step_read') }}
                                </span>
                                <span
                                    class="border border-[#bfdbfe] bg-[#eff6ff] px-3 py-2 text-xs font-black tracking-[0.14em] text-[#1d4ed8] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.cta_step_save') }}
                                </span>
                                <span
                                    class="border border-[#bfdbfe] bg-[#eff6ff] px-3 py-2 text-xs font-black tracking-[0.14em] text-[#1d4ed8] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.cta_step_share') }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <div class="border border-[#bfdbfe] bg-white p-4">
                                <p
                                    class="text-xs font-black tracking-[0.16em] text-[#2563eb] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_search_label') }}
                                </p>
                                <div
                                    class="mt-4 border border-[#dbeafe] bg-[#f8fbff] p-3"
                                >
                                    <div class="h-3 w-3/4 bg-[#1d4ed8]"></div>
                                    <div
                                        class="mt-3 grid grid-cols-[1fr_auto] gap-3"
                                    >
                                        <span
                                            class="h-8 border border-[#bfdbfe] bg-white"
                                        ></span>
                                        <span
                                            class="h-8 w-16 bg-[#f59e0b]"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="border border-[#fde68a] bg-[#fffbeb] p-4"
                            >
                                <p
                                    class="text-xs font-black tracking-[0.16em] text-[#b45309] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_queue_label') }}
                                </p>
                                <div class="mt-4 grid gap-2">
                                    <div
                                        class="grid grid-cols-[auto_1fr] gap-3"
                                    >
                                        <span class="font-black text-[#f59e0b]">
                                            01
                                        </span>
                                        <span
                                            class="h-3 self-center bg-[#1d4ed8]"
                                        ></span>
                                    </div>
                                    <div
                                        class="grid grid-cols-[auto_1fr] gap-3"
                                    >
                                        <span class="font-black text-[#f59e0b]">
                                            02
                                        </span>
                                        <span
                                            class="h-3 self-center bg-[#93c5fd]"
                                        ></span>
                                    </div>
                                    <div
                                        class="grid grid-cols-[auto_1fr] gap-3"
                                    >
                                        <span class="font-black text-[#f59e0b]">
                                            03
                                        </span>
                                        <span
                                            class="h-3 self-center bg-[#bfdbfe]"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-[#dbeafe] bg-white p-4">
                                <p
                                    class="text-xs font-black tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_editor_label') }}
                                </p>
                                <p
                                    class="mt-2 text-2xl font-black text-[#111827]"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_editor_value') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
