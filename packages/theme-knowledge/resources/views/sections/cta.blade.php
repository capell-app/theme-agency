@php
    $actions ??= $section->actions ?? [];
@endphp

<section
    class="theme-section theme-section-cta knowledge-cta bg-[var(--site-theme-ink)]"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-8 lg:grid-cols-[0.8fr_1fr] lg:items-center">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-accent)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.cta_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight text-white">
                    {{ $heading ?? $section->heading }}
                </h2>
                <p class="mt-4 max-w-2xl text-slate-300">
                    {{ $summary ?? $section->summary ?? __('capell-theme-knowledge::generic.cta_copy') }}
                </p>
            </div>

            <div
                class="border border-white/10 bg-[var(--site-theme-ink-panel)] p-5 shadow-xl shadow-black/20"
            >
                <div class="grid gap-3 sm:grid-cols-3">
                    @foreach ([
                                  __('capell-theme-knowledge::generic.cta_step_read'),
                                  __('capell-theme-knowledge::generic.cta_step_save'),
                                  __('capell-theme-knowledge::generic.cta_step_share'),
                              ] as $step)
                        <div class="border border-white/10 bg-white/5 p-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary-soft)] uppercase"
                            >
                                {{ $step }}
                            </p>
                            <span
                                class="mt-4 block h-1 bg-[var(--site-theme-accent)]"
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
                                class="{{ ($action['style'] ?? 'primary') === 'primary' ? 'bg-[var(--site-theme-accent)] text-[var(--site-theme-ink)] hover:bg-[var(--site-theme-accent-strong)]' : 'border border-white/20 bg-white/5 text-white hover:border-[var(--site-theme-accent)]' }} px-5 py-3 text-sm font-black transition"
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
