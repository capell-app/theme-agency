@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features bg-[#f4fbf9]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 lg:grid-cols-[0.72fr_1fr] lg:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#0f766e] uppercase"
                >
                    {{ __('capell-theme-local-services::generic.dispatch_board_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#17211c]"
                >
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-600 lg:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($features as $index => $feature)
                <article
                    class="group relative overflow-hidden border border-[#a7f3d0] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#0f766e] hover:shadow-xl"
                >
                    <div class="mb-5 border border-[#d1fae5] bg-[#f0fdfa] p-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-[#0f766e]">
                                {{ __('capell-theme-local-services::generic.job_signal') }}
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span
                                class="rounded-full bg-[#ffedd5] px-3 py-1 text-xs font-black text-[#c2410c]"
                            >
                                {{ __('capell-theme-local-services::generic.quote_signal') }}
                            </span>
                        </div>
                        <div
                            class="mt-4 grid grid-cols-[1fr_2rem_1fr] items-center gap-2"
                            aria-hidden="true"
                        >
                            <span class="h-3 bg-[#134e4a]"></span>
                            <span class="h-3 rounded-full bg-[#f97316]"></span>
                            <span class="h-3 bg-[#99f6e4]"></span>
                        </div>
                        <div
                            class="mt-3 grid grid-cols-3 gap-2"
                            aria-hidden="true"
                        >
                            <span class="h-10 bg-white"></span>
                            <span class="h-10 bg-white"></span>
                            <span class="h-10 bg-white"></span>
                        </div>
                    </div>

                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#0f766e] uppercase"
                    >
                        {{ $feature['type'] ?? __('capell-theme-local-services::generic.route_signal') }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#17211c]">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="bg-[#ccfbf1] px-3 py-1 text-[#115e59]">
                            {{ __('capell-theme-local-services::generic.area_signal') }}
                        </span>
                        <span class="bg-[#ffedd5] px-3 py-1 text-[#9a3412]">
                            {{ __('capell-theme-local-services::generic.response_signal') }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
