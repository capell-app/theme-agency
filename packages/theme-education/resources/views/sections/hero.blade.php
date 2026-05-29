@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-education::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-education::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-education::generic.hero_primary_action'),
        'url' => '#courses',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-education::generic.hero_secondary_action'),
        'url' => '#apply',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? '');
@endphp

<section
    class="theme-section theme-section-hero bg-[#f8fbff] px-6 py-16 lg:py-20"
>
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.86fr_1.14fr] lg:items-center"
        >
            <div>
                <p class="text-xs font-black text-[#0f766e] uppercase">
                    {{ $eyebrow }}
                </p>
                <h2
                    class="mt-5 max-w-3xl text-5xl leading-tight font-black tracking-normal text-[#020617] lg:text-6xl"
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
                        href="{{ $primaryAction['url'] ?? '#courses' }}"
                        class="inline-flex bg-[#1d4ed8] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-education::generic.hero_primary_action') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#apply' }}"
                        class="inline-flex border border-indigo-200 bg-white px-5 py-3 text-sm font-black text-[#1d4ed8]"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-education::generic.hero_secondary_action') }}
                    </a>
                </div>

                <dl
                    class="mt-8 grid max-w-xl grid-cols-3 border border-indigo-100 bg-white"
                >
                    <div class="p-4">
                        <dt class="text-xs font-black text-[#0f766e] uppercase">
                            {{ __('capell-theme-education::generic.hero_track_label') }}
                        </dt>
                        <dd class="mt-2 text-xl font-black text-[#020617]">
                            {{ __('capell-theme-education::generic.hero_track_value') }}
                        </dd>
                    </div>
                    <div class="border-l border-indigo-100 p-4">
                        <dt class="text-xs font-black text-[#0f766e] uppercase">
                            {{ __('capell-theme-education::generic.hero_mentor_label') }}
                        </dt>
                        <dd class="mt-2 text-xl font-black text-[#020617]">
                            {{ __('capell-theme-education::generic.hero_mentor_value') }}
                        </dd>
                    </div>
                    <div class="border-l border-indigo-100 p-4">
                        <dt class="text-xs font-black text-[#0f766e] uppercase">
                            {{ __('capell-theme-education::generic.hero_outcome_label') }}
                        </dt>
                        <dd class="mt-2 text-xl font-black text-[#020617]">
                            {{ __('capell-theme-education::generic.hero_outcome_value') }}
                        </dd>
                    </div>
                </dl>
            </div>

            <div
                class="border border-indigo-100 bg-white p-3 shadow-2xl shadow-indigo-950/10"
            >
                <div class="grid gap-3 lg:grid-cols-[1fr_0.52fr]">
                    <div class="bg-[#eef6ff] p-4">
                        @if ($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $imageAlt }}"
                                class="aspect-[16/10] w-full object-cover"
                            />
                        @else
                            <div
                                class="grid aspect-[16/10] bg-white p-5"
                                aria-hidden="true"
                            >
                                <div
                                    class="grid gap-4 sm:grid-cols-[0.8fr_1.2fr]"
                                >
                                    <div class="space-y-3">
                                        <span
                                            class="block h-4 w-20 bg-[#4338ca]"
                                        ></span>
                                        <span
                                            class="block h-3 w-28 bg-sky-300"
                                        ></span>
                                        <span
                                            class="block h-14 bg-slate-100"
                                        ></span>
                                    </div>
                                    <div class="space-y-3">
                                        <span
                                            class="block h-6 bg-white shadow-sm"
                                        ></span>
                                        <span
                                            class="block h-8 bg-teal-100"
                                        ></span>
                                        <span
                                            class="block h-8 bg-indigo-50"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-3">
                        <div class="border border-indigo-100 bg-[#ecfeff] p-4">
                            <p
                                class="text-xs font-black text-[#0f766e] uppercase"
                            >
                                {{ __('capell-theme-education::generic.curriculum_label') }}
                            </p>
                            <p class="mt-3 text-lg font-black text-[#020617]">
                                {{ __('capell-theme-education::generic.curriculum_value') }}
                            </p>
                        </div>
                        <div class="border border-indigo-100 bg-[#eef2ff] p-4">
                            <p
                                class="text-xs font-black text-[#4338ca] uppercase"
                            >
                                {{ __('capell-theme-education::generic.mentor_path_label') }}
                            </p>
                            <p class="mt-3 text-lg font-black text-[#020617]">
                                {{ __('capell-theme-education::generic.mentor_path_value') }}
                            </p>
                        </div>
                        <div class="border border-indigo-100 bg-[#fff7ed] p-4">
                            <p
                                class="text-xs font-black text-[#c2410c] uppercase"
                            >
                                {{ __('capell-theme-education::generic.enrolment_cta_label') }}
                            </p>
                            <div
                                class="mt-4 grid grid-cols-3 gap-2"
                                aria-hidden="true"
                            >
                                <span class="h-8 bg-[#1d4ed8]"></span>
                                <span class="h-8 bg-[#14b8a6]"></span>
                                <span class="h-8 bg-[#fb923c]"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
