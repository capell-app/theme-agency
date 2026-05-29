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

<section class="theme-section theme-section-hero bg-white px-6 py-20">
    @isset($heading)
        <div
            class="mx-auto grid max-w-6xl gap-10 lg:grid-cols-[0.95fr_1.05fr] lg:items-center"
        >
            <div class="space-y-5">
                <p
                    class="text-xs font-black tracking-[0.18em] text-teal-600 uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h2
                    class="max-w-3xl text-5xl leading-tight font-black tracking-normal text-[#020617]"
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
                        href="{{ $primaryAction['url'] ?? '#courses' }}"
                        class="inline-flex rounded-full bg-[#1d4ed8] px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-education::generic.hero_primary_action') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#apply' }}"
                        class="inline-flex rounded-full border border-indigo-200 bg-white px-5 py-3 text-sm font-black text-[#1d4ed8]"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-education::generic.hero_secondary_action') }}
                    </a>
                </div>
            </div>

            <div
                class="border border-indigo-100 bg-[#f8fbff] p-3 shadow-xl shadow-indigo-950/5"
            >
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
                        <div class="grid gap-4 sm:grid-cols-[0.8fr_1.2fr]">
                            <div class="space-y-3">
                                <span
                                    class="block h-4 w-20 rounded-full bg-[#4338ca]"
                                ></span>
                                <span
                                    class="block h-3 w-28 rounded-full bg-sky-300"
                                ></span>
                                <span
                                    class="block h-14 rounded-md bg-slate-100"
                                ></span>
                            </div>
                            <div class="space-y-3">
                                <span
                                    class="block h-6 rounded-md bg-white shadow-sm"
                                ></span>
                                <span
                                    class="block h-8 rounded-md bg-teal-100"
                                ></span>
                                <span
                                    class="block h-8 rounded-md bg-indigo-50"
                                ></span>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="grid border-t border-indigo-100 sm:grid-cols-3">
                    <div class="p-4">
                        <p
                            class="text-xs font-black tracking-[0.16em] text-teal-600 uppercase"
                        >
                            {{ __('capell-theme-education::generic.hero_track_label') }}
                        </p>
                        <p class="mt-2 text-xl font-black text-[#020617]">
                            {{ __('capell-theme-education::generic.hero_track_value') }}
                        </p>
                    </div>
                    <div
                        class="border-t border-indigo-100 p-4 sm:border-t-0 sm:border-l"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-teal-600 uppercase"
                        >
                            {{ __('capell-theme-education::generic.hero_mentor_label') }}
                        </p>
                        <p class="mt-2 text-xl font-black text-[#020617]">
                            {{ __('capell-theme-education::generic.hero_mentor_value') }}
                        </p>
                    </div>
                    <div
                        class="border-t border-indigo-100 p-4 sm:border-t-0 sm:border-l"
                    >
                        <p
                            class="text-xs font-black tracking-[0.16em] text-teal-600 uppercase"
                        >
                            {{ __('capell-theme-education::generic.hero_outcome_label') }}
                        </p>
                        <p class="mt-2 text-xl font-black text-[#020617]">
                            {{ __('capell-theme-education::generic.hero_outcome_value') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
