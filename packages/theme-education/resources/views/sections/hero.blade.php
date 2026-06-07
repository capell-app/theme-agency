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
    class="education-hero theme-section theme-section-hero bg-[#f8fbff] px-6 py-16 lg:py-20"
>
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.86fr_1.14fr] lg:items-center"
        >
            <div>
                <p
                    class="text-xs font-semibold tracking-[0.1em] text-[#0f766e] uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h1
                    class="mt-5 max-w-3xl text-4xl leading-tight font-extrabold tracking-normal text-[#020617] sm:text-5xl"
                >
                    {{ $heading }}
                </h1>
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
                    class="education-cohort-strip mt-8 grid max-w-xl grid-cols-3"
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

            <div class="education-learning-board bg-white p-3">
                <div class="grid gap-3 lg:grid-cols-[1fr_0.52fr]">
                    <div class="education-media-frame bg-[#eef6ff] p-4">
                        @if ($imageUrl)
                            <img
                                src="{{ $imageUrl }}"
                                alt="{{ $imageAlt }}"
                                width="1200"
                                height="750"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                                sizes="(min-width: 1024px) 52vw, 100vw"
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
                        <div class="education-path-card bg-[#ecfeff] p-4">
                            <p
                                class="text-xs font-black text-[#0f766e] uppercase"
                            >
                                {{ __('capell-theme-education::generic.curriculum_label') }}
                            </p>
                            <p class="mt-3 text-lg font-black text-[#020617]">
                                {{ __('capell-theme-education::generic.curriculum_value') }}
                            </p>
                            <div
                                class="mt-4 grid gap-1.5"
                                aria-hidden="true"
                            >
                                <span class="h-1.5 w-4/5 bg-[#0f766e]"></span>
                                <span
                                    class="h-1.5 w-2/3 bg-[#0f766e]/30"
                                ></span>
                            </div>
                        </div>
                        <div class="education-path-card bg-[#eef2ff] p-4">
                            <p
                                class="text-xs font-black text-[#4338ca] uppercase"
                            >
                                {{ __('capell-theme-education::generic.mentor_path_label') }}
                            </p>
                            <p class="mt-3 text-lg font-black text-[#020617]">
                                {{ __('capell-theme-education::generic.mentor_path_value') }}
                            </p>
                            <div
                                class="mt-4 grid grid-cols-4 gap-1.5"
                                aria-hidden="true"
                            >
                                <span class="h-8 bg-[#4338ca]/20"></span>
                                <span class="h-8 bg-[#4338ca]/35"></span>
                                <span class="h-8 bg-[#4338ca]/50"></span>
                                <span class="h-8 bg-[#4338ca]"></span>
                            </div>
                        </div>
                        <div class="education-path-card bg-[#fff7ed] p-4">
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
                            <p class="mt-3 text-xs font-black text-[#c2410c]">
                                {{ __('capell-theme-education::generic.enrolment_ready_label') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
