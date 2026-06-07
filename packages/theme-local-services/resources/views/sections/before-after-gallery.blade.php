@php
    $projects = $section->projects ?? $section->items ?? __('capell-theme-local-services::generic.before_after_defaults');
    $heading ??= $section->heading ?? __('capell-theme-local-services::generic.before_after_label');
    $summary ??= $section->summary ?? __('capell-theme-local-services::generic.before_after_summary');
@endphp

<section class="theme-section theme-section-before-after-gallery bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div class="grid gap-5 md:grid-cols-[0.75fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.before_after_label') }}
                </p>
                <h2
                    class="mt-3 text-3xl font-black tracking-tight text-[#13231f]"
                >
                    {{ $heading }}
                </h2>
            </div>

            @if ($summary)
                <p class="text-base leading-8 text-slate-600">
                    {{ $summary }}
                </p>
            @endif
        </div>

        <div class="mt-9 grid gap-5 lg:grid-cols-2">
            @forelse ($projects as $project)
                @php
                    $title = $project['title'] ?? $project['name'] ?? __('capell-theme-local-services::generic.before_after_project_fallback');
                    $projectSummary = $project['summary'] ?? $project['description'] ?? null;
                    $location = $project['location'] ?? $project['area'] ?? null;
                    $service = $project['service'] ?? $project['category'] ?? null;
                    $projectUrl = $project['url'] ?? null;
                    $beforeImage = $project['beforeImage'] ?? $project['before_image'] ?? null;
                    $afterImage = $project['afterImage'] ?? $project['after_image'] ?? null;
                    $beforeAlt = $project['beforeAlt'] ?? $project['before_alt'] ?? __('capell-theme-local-services::generic.before_image_alt', ['project' => $title]);
                    $afterAlt = $project['afterAlt'] ?? $project['after_alt'] ?? __('capell-theme-local-services::generic.after_image_alt', ['project' => $title]);
                @endphp

                <article
                    class="overflow-hidden rounded-xl border border-slate-200 bg-[#f7fbf8]"
                >
                    <div class="grid sm:grid-cols-2">
                        <figure class="relative min-h-48 bg-slate-200">
                            @if ($beforeImage)
                                <img
                                    src="{{ $beforeImage }}"
                                    alt="{{ $beforeAlt }}"
                                    width="720"
                                    height="540"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full min-h-48 w-full object-cover"
                                />
                            @endif

                            <figcaption
                                class="absolute top-3 left-3 bg-[#13231f] px-3 py-1 text-xs font-black tracking-[0.14em] text-white uppercase"
                            >
                                {{ __('capell-theme-local-services::generic.before_label') }}
                            </figcaption>
                        </figure>

                        <figure class="relative min-h-48 bg-slate-200">
                            @if ($afterImage)
                                <img
                                    src="{{ $afterImage }}"
                                    alt="{{ $afterAlt }}"
                                    width="720"
                                    height="540"
                                    loading="lazy"
                                    decoding="async"
                                    class="h-full min-h-48 w-full object-cover"
                                />
                            @endif

                            <figcaption
                                class="absolute top-3 left-3 bg-[#0f766e] px-3 py-1 text-xs font-black tracking-[0.14em] text-white uppercase"
                            >
                                {{ __('capell-theme-local-services::generic.after_label') }}
                            </figcaption>
                        </figure>
                    </div>

                    <div class="p-5">
                        <p
                            class="text-xs font-black tracking-[0.14em] text-[#f97316] uppercase"
                        >
                            {{ collect([$service, $location])->filter()->join(' · ') ?: __('capell-theme-local-services::generic.case_study_metric_fallback') }}
                        </p>
                        <h3 class="mt-2 text-xl font-black text-[#13231f]">
                            @if ($projectUrl)
                                <a
                                    href="{{ $projectUrl }}"
                                    class="hover:text-[#0f766e]"
                                >
                                    {{ $title }}
                                </a>
                            @else
                                {{ $title }}
                            @endif
                        </h3>

                        @if ($projectSummary)
                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $projectSummary }}
                            </p>
                        @endif
                    </div>
                </article>
            @empty
                <article
                    class="rounded-xl border border-dashed border-slate-300 bg-[#f7fbf8] p-6 lg:col-span-2"
                >
                    <h3 class="text-lg font-black text-[#13231f]">
                        {{ __('capell-theme-local-services::generic.before_after_empty_title') }}
                    </h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ __('capell-theme-local-services::generic.before_after_empty_summary') }}
                    </p>
                </article>
            @endforelse
        </div>
    </div>
</section>
