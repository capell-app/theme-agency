@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section class="theme-section theme-section-features bg-[#fff8e7]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 md:grid-cols-[0.82fr_1fr] md:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#b45309] uppercase"
                >
                    {{ __('capell-theme-nonprofit::generic.impact_paths_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#132014]"
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

        <div class="mt-10 grid gap-5 md:grid-cols-12">
            @foreach ($features as $feature)
                <article
                    class="group border border-[#facc15] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:shadow-xl md:col-span-4 even:md:translate-y-8"
                >
                    <div class="mb-5 bg-[#12351f] p-4 text-white">
                        <div class="flex items-end justify-between gap-4">
                            <div>
                                <p
                                    class="text-[0.65rem] font-black tracking-[0.18em] text-[#fde047] uppercase"
                                >
                                    {{ $feature['type'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                                </p>
                                <p class="mt-8 text-4xl font-black">
                                    {{ $loop->iteration * 12 }}%
                                </p>
                            </div>
                            <span
                                class="block h-16 w-16 rounded-full border-8 border-[#fde047]"
                                aria-hidden="true"
                            ></span>
                        </div>
                    </div>

                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#b45309] uppercase"
                    >
                        {{ $feature['type'] ?? __('capell-theme-nonprofit::generic.impact_signal') }}
                    </p>
                    <h3 class="mt-3 text-lg font-black text-[#132014]">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold">
                        <span class="bg-[#fef3c7] px-3 py-1 text-[#92400e]">
                            {{ __('capell-theme-nonprofit::generic.donor_signal') }}
                        </span>
                        <span class="bg-[#dcfce7] px-3 py-1 text-[#166534]">
                            {{ __('capell-theme-nonprofit::generic.action_signal') }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
