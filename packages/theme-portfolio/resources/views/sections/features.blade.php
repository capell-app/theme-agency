@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-5 md:grid-cols-[0.65fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#1f3173] uppercase"
                >
                    {{ __('capell-theme-portfolio::generic.capabilities_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#0f172a]"
                >
                    {{ $section->heading }}
                </h2>
            </div>

            @if ($section->summary ?? null)
                <p class="max-w-2xl text-lg text-stone-600 md:justify-self-end">
                    {{ $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($features as $feature)
                <article
                    class="group border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[#1f3173] hover:shadow-xl"
                >
                    <div
                        class="mb-5 flex aspect-[4/2.1] items-end overflow-hidden bg-[#070b1a] p-4 text-white"
                    >
                        <div class="w-full">
                            <div
                                class="flex items-center justify-between gap-3"
                            >
                                <span
                                    class="text-[0.65rem] font-black tracking-[0.18em] text-[#fb923c] uppercase"
                                >
                                    {{ $feature['type'] ?? $feature['icon'] ?? __('capell-theme-portfolio::generic.capability_signal') }}
                                </span>
                                <span
                                    class="h-2 w-8 rounded-full bg-[#fb923c]"
                                    aria-hidden="true"
                                ></span>
                            </div>
                            <div
                                class="mt-6 grid grid-cols-3 gap-2"
                                aria-hidden="true"
                            >
                                <span class="h-8 bg-white/20"></span>
                                <span class="h-8 bg-[#1f3173]/80"></span>
                                <span class="h-8 bg-white/10"></span>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-black text-[#0f172a]">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm text-stone-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <p
                        class="mt-5 text-xs font-black tracking-[0.18em] text-[#1f3173] uppercase"
                    >
                        {{ $feature['metric'] ?? __('capell-theme-portfolio::generic.proof_signal') }}
                    </p>
                </article>
            @endforeach
        </div>
    </div>
</section>
