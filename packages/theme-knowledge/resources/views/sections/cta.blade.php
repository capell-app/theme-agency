@php
    $actions ??= $section->actions ?? [];
@endphp

<section class="theme-section theme-section-cta bg-[#f8fafc]">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.8fr_1fr] lg:items-center">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#1d4ed8] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.cta_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[#111827]"
                >
                    {{ $heading ?? $section->heading }}
                </h2>
                <p class="mt-4 max-w-2xl text-slate-600">
                    {{ $summary ?? $section->summary ?? __('capell-theme-knowledge::generic.cta_copy') }}
                </p>
            </div>

            <div class="border border-[#bfdbfe] bg-white p-5 shadow-xl">
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                                  __('capell-theme-knowledge::generic.cta_step_read'),
                                  __('capell-theme-knowledge::generic.cta_step_save'),
                                  __('capell-theme-knowledge::generic.cta_step_share'),
                              ] as $step)
                        <div class="border border-[#dbeafe] bg-[#eff6ff] p-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[#1d4ed8] uppercase"
                            >
                                {{ $step }}
                            </p>
                            <span
                                class="mt-4 block h-1 bg-[#f59e0b]"
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
                                class="{{ ($action['style'] ?? 'primary') === 'primary' ? 'bg-[#1d4ed8] text-white hover:bg-[#1e40af]' : 'border border-slate-300 bg-white text-[#111827] hover:border-[#1d4ed8]' }} px-5 py-3 text-sm font-black transition"
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
