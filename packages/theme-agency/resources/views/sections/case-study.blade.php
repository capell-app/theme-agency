@php
    $metrics = $section->metrics ?? $section->stats ?? [];
    $gallery = $section->gallery ?? $section->images ?? [];
    $hasStory = filled($section->challenge ?? null) || filled($section->approach ?? null) || filled($section->result ?? null);
    $hasContent = $hasStory || $metrics !== [] || $gallery !== [] || filled($section->mediaUrl ?? null) || filled($section->image ?? null);
@endphp

<section class="theme-case-study mx-auto max-w-7xl px-6 py-20">
    <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:items-end">
        <div>
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.case_study_signal') }}
            </p>
            <h2 class="mt-4 max-w-4xl text-5xl font-black tracking-tight">
                {{ $section->heading }}
            </h2>
        </div>

        <div class="grid gap-4">
            @if ($section->summary)
                <p class="max-w-2xl text-zinc-400 lg:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif

            <div
                class="flex flex-wrap gap-2 text-xs font-black tracking-[0.18em] uppercase"
            >
                @foreach (array_filter([$section->client ?? null, $section->discipline ?? null, $section->year ?? null]) as $label)
                    <span
                        class="rounded-full border border-white/15 px-4 py-2 text-zinc-300"
                    >
                        {{ $label }}
                    </span>
                @endforeach
            </div>
        </div>
    </div>

    @if ($hasContent)
        <div
            class="mt-10 overflow-hidden rounded-[2rem] bg-white p-4 text-zinc-950 shadow-sm"
        >
            @if (! empty($section->mediaUrl) || ! empty($section->image))
                <img
                    src="{{ $section->mediaUrl ?? $section->image }}"
                    alt="{{ $section->mediaAlt ?? '' }}"
                    width="1200"
                    height="800"
                    loading="lazy"
                    decoding="async"
                    class="aspect-[16/9] w-full rounded-[1.5rem] object-cover"
                />
            @else
                <div
                    class="site-brand-gradient aspect-[16/9] rounded-[1.5rem] p-5"
                    aria-hidden="true"
                >
                    <div
                        class="flex h-full flex-col justify-between rounded-3xl bg-zinc-950/90 p-5"
                    >
                        <div class="flex items-center justify-between">
                            <span class="h-3 w-24 rounded-full bg-white"></span>
                            <span
                                class="h-3 w-12 rounded-full bg-[var(--theme-accent)]"
                            ></span>
                        </div>
                        <div class="grid grid-cols-4 gap-3">
                            <span class="h-16 rounded-2xl bg-white/15"></span>
                            <span class="h-16 rounded-2xl bg-white/25"></span>
                            <span class="h-16 rounded-2xl bg-white/15"></span>
                            <span class="h-16 rounded-2xl bg-white/25"></span>
                        </div>
                    </div>
                </div>
            @endif

            <div class="grid gap-4 p-2 pt-6 lg:grid-cols-3">
                @foreach ([
                              __('capell-theme-agency::generic.case_study_challenge_label') => $section->challenge ?? null,
                              __('capell-theme-agency::generic.case_study_approach_label') => $section->approach ?? null,
                              __('capell-theme-agency::generic.case_study_result_label') => $section->result ?? null,
                          ] as $label => $copy)
                    @if ($copy)
                        <article class="rounded-[1.25rem] bg-zinc-100 p-5">
                            <p
                                class="text-xs font-black tracking-[0.18em] text-[var(--theme-primary)] uppercase"
                            >
                                {{ $label }}
                            </p>
                            <p class="mt-3 text-sm leading-6 text-zinc-600">
                                {{ $copy }}
                            </p>
                        </article>
                    @endif
                @endforeach
            </div>

            @if ($metrics !== [])
                <div
                    class="grid gap-px overflow-hidden rounded-[1.25rem] bg-zinc-200 text-zinc-950 sm:grid-cols-3"
                >
                    @foreach ($metrics as $metric)
                        <div class="bg-white p-5">
                            <p
                                class="text-xs font-black tracking-[0.18em] text-zinc-500 uppercase"
                            >
                                {{ $metric['label'] ?? __('capell-theme-agency::generic.case_study_metric_label') }}
                            </p>
                            <p class="mt-3 text-3xl font-black tracking-tight">
                                {{ $metric['value'] ?? $metric['metric'] ?? '' }}
                            </p>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        @if ($gallery !== [])
            <div class="mt-8">
                <p
                    class="text-xs font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
                >
                    {{ __('capell-theme-agency::generic.case_study_gallery_label') }}
                </p>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach ($gallery as $image)
                        <img
                            src="{{ $image['url'] ?? $image['image'] ?? $image['src'] ?? '' }}"
                            alt="{{ $image['alt'] ?? '' }}"
                            width="720"
                            height="540"
                            loading="lazy"
                            decoding="async"
                            class="aspect-[4/3] rounded-[1.25rem] object-cover"
                        />
                    @endforeach
                </div>
            </div>
        @endif
    @else
        <div
            class="mt-10 rounded-[1.5rem] border border-dashed border-white/20 p-8"
        >
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.case_study_empty_title') }}
            </p>
            <p class="mt-3 max-w-xl text-sm leading-6 text-zinc-400">
                {{ __('capell-theme-agency::generic.case_study_empty_summary') }}
            </p>
        </div>
    @endif
</section>
