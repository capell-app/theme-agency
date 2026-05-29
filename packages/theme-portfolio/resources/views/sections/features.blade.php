@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16 lg:py-20">
        <div class="grid gap-5 md:grid-cols-[0.58fr_1fr] md:items-end">
            <div>
                <p class="text-xs font-black text-[#1f3173] uppercase">
                    {{ __('capell-theme-portfolio::generic.capabilities_label') }}
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

        <div class="mt-10 grid gap-4 lg:grid-cols-3">
            @foreach ($features as $feature)
                <article
                    class="group grid min-h-full grid-rows-[auto_1fr] border border-slate-200 bg-[#f8fafc] transition hover:-translate-y-1 hover:border-[#1f3173] hover:bg-white hover:shadow-xl"
                >
                    <div
                        class="border-b border-slate-200 bg-[#070b1a] p-5 text-white"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p
                                    class="text-xs font-black text-[#fb923c] uppercase"
                                >
                                    {{ __('capell-theme-portfolio::generic.capability_signal') }}
                                </p>
                                <p class="mt-3 text-3xl font-black">
                                    {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                </p>
                            </div>
                            <div
                                class="grid w-24 grid-cols-3 gap-1"
                                aria-hidden="true"
                            >
                                <span class="h-8 bg-white/20"></span>
                                <span class="h-8 bg-[#1f3173]"></span>
                                <span class="h-8 bg-white/10"></span>
                            </div>
                        </div>
                    </div>

                    <div class="p-5">
                        <p class="text-xs font-black text-[#1f3173] uppercase">
                            {{ $feature['metric'] ?? __('capell-theme-portfolio::generic.proof_signal') }}
                        </p>
                        <h3 class="mt-3 text-xl font-black text-[#0f172a]">
                            {{ $feature['title'] }}
                        </h3>
                        <p class="mt-3 text-sm leading-6 text-slate-600">
                            {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                        </p>
                        <div
                            class="mt-5 grid grid-cols-3 border border-slate-200 bg-white text-center text-[0.68rem] font-black text-slate-500"
                        >
                            <span class="p-2">
                                {{ __('capell-theme-portfolio::generic.brief_label') }}
                            </span>
                            <span class="border-l border-slate-200 p-2">
                                {{ __('capell-theme-portfolio::generic.build_label') }}
                            </span>
                            <span class="border-l border-slate-200 p-2">
                                {{ __('capell-theme-portfolio::generic.publish_label') }}
                            </span>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
