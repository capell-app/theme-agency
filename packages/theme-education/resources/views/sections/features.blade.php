@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features bg-[#f8fbff]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 md:grid-cols-[0.7fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#14b8a6] uppercase"
                >
                    {{ __('capell-theme-education::generic.learning_pathways_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                >
                    {{ $section->heading }}
                </h2>
            </div>

            @if ($section->summary ?? null)
                <p class="max-w-2xl text-lg text-slate-600 md:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($features as $feature)
                <article
                    class="group border border-[#c7d2fe] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#4338ca] hover:shadow-xl"
                >
                    <div
                        class="mb-5 overflow-hidden border border-[#dbeafe] bg-[#eef6ff]"
                    >
                        <div
                            class="grid min-h-28 grid-cols-[0.9fr_1.1fr] gap-3 p-4"
                            aria-hidden="true"
                        >
                            <div class="space-y-2">
                                <span
                                    class="block h-3 w-14 bg-[#4338ca]"
                                ></span>
                                <span
                                    class="block h-3 w-20 bg-[#93c5fd]"
                                ></span>
                                <span class="block h-10 bg-white"></span>
                            </div>
                            <div class="grid grid-rows-3 gap-2">
                                <span class="bg-white"></span>
                                <span class="bg-[#14b8a6]/25"></span>
                                <span class="bg-white"></span>
                            </div>
                        </div>
                    </div>

                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#4338ca] uppercase"
                    >
                        {{ $feature['type'] ?? __('capell-theme-education::generic.course_signal') }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#0f172a]">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="bg-[#ecfeff] px-3 py-1 text-[#0f766e]">
                            {{ __('capell-theme-education::generic.course_format_signal') }}
                        </span>
                        <span class="bg-[#eef2ff] px-3 py-1 text-[#4338ca]">
                            {{ __('capell-theme-education::generic.course_outcome_signal') }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
