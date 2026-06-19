@php
    $actions ??= $section->actions ?? [];
@endphp

<section class="theme-section theme-section-cta bg-[#10201d]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.85fr_1fr] lg:items-center">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#fb923c] uppercase"
                >
                    {{ __('capell-theme-dog-walkers::generic.cta_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-white">
                    {{ $heading ?? $section->heading }}
                </h2>
                <p class="mt-4 max-w-2xl text-slate-300">
                    {{ $summary ?? $section->summary ?? __('capell-theme-dog-walkers::generic.cta_copy') }}
                </p>
            </div>

            <div
                class="border border-white/15 bg-white p-5 text-[#17211c] shadow-2xl"
            >
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                                  __('capell-theme-dog-walkers::generic.cta_step_scope'),
                                  __('capell-theme-dog-walkers::generic.cta_step_slot'),
                                  __('capell-theme-dog-walkers::generic.cta_step_confirm'),
                              ] as $step)
                        <div class="border border-[#ccfbf1] bg-[#f0fdfa] p-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#0f766e] uppercase"
                            >
                                {{ $step }}
                            </p>
                            <span
                                class="mt-4 block h-2 bg-[#f97316]"
                                aria-hidden="true"
                            ></span>
                        </div>
                    @endforeach
                </div>

                @if ($actions !== [])
                    <div class="mt-5 flex flex-wrap gap-3">
                        @foreach ($actions as $action)
                            <a
                                href="{{ $action['url'] ?? '#' }}"
                                class="{{ ($action['style'] ?? 'primary') === 'primary' ? 'bg-[#0f766e] text-white hover:bg-[#115e59]' : 'border border-slate-300 bg-white text-[#17211c] hover:border-[#0f766e]' }} px-5 py-3 text-sm font-black transition"
                            >
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>
