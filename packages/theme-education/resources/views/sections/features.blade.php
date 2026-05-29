@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features bg-[#f8fbff]">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-6 md:grid-cols-[0.66fr_1fr] md:items-end">
            <div>
                <p class="text-xs font-black text-[#0f766e] uppercase">
                    {{ __('capell-theme-education::generic.learning_pathways_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                >
                    {{ $section->heading }}
                </h2>
            </div>

            @if ($section->summary ?? null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-600 md:justify-self-end"
                >
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($features as $feature)
                <article
                    class="group grid min-h-full grid-rows-[auto_1fr] border border-[#c7d2fe] bg-white shadow-sm transition hover:-translate-y-1 hover:border-[#4338ca] hover:shadow-xl"
                >
                    <div class="border-b border-[#dbeafe] bg-[#eef6ff] p-5">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-black text-[#4338ca] uppercase"
                                >
                                    {{ $feature['type'] ?? __('capell-theme-education::generic.course_signal') }}
                                </p>
                                <p
                                    class="mt-3 text-3xl font-black text-[#020617]"
                                >
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>
                            <div
                                class="grid w-28 grid-cols-3 gap-1"
                                aria-hidden="true"
                            >
                                <span class="h-8 bg-white"></span>
                                <span class="h-8 bg-[#14b8a6]/35"></span>
                                <span class="h-8 bg-[#4338ca]/20"></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <p class="text-xs font-black text-[#0f766e] uppercase">
                            {{ __('capell-theme-education::generic.pathway_step_label') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-[#0f172a]">
                            {{ $feature['title'] }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                        </p>
                        <div
                            class="mt-5 grid grid-cols-3 border border-[#dbeafe] bg-[#f8fbff] text-center text-[0.68rem] font-black text-slate-600"
                        >
                            <span class="p-2">
                                {{ __('capell-theme-education::generic.enrolment_step_one') }}
                            </span>
                            <span class="border-l border-[#dbeafe] p-2">
                                {{ __('capell-theme-education::generic.enrolment_step_two') }}
                            </span>
                            <span class="border-l border-[#dbeafe] p-2">
                                {{ __('capell-theme-education::generic.enrolment_step_three') }}
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
