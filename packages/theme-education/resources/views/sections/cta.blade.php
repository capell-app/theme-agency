<section class="theme-section theme-section-cta bg-white">
    <div class="mx-auto max-w-6xl px-6 py-16">
        <div
            class="grid gap-8 bg-[#050b24] p-8 text-white md:grid-cols-[0.72fr_1fr] md:items-center md:p-10"
        >
            <div>
                <p class="text-xs font-black text-[#5eead4] uppercase">
                    {{ __('capell-theme-education::generic.enrolment_cta_label') }}
                </p>
                <h2 class="mt-4 text-4xl font-black tracking-tight">
                    {{ $section->heading }}
                </h2>
            </div>

            <div>
                <p class="max-w-2xl text-base leading-7 text-slate-300">
                    {{ $section->summary ?? __('capell-theme-education::generic.cta_blurb') }}
                </p>

                <div
                    class="mt-6 grid gap-3 bg-[#0f1b3d] p-4 sm:grid-cols-3"
                    aria-hidden="true"
                >
                    <span
                        class="bg-white/10 p-3 text-xs font-black text-white/70 uppercase"
                    >
                        {{ __('capell-theme-education::generic.enrolment_step_one') }}
                    </span>
                    <span
                        class="bg-white/10 p-3 text-xs font-black text-white/70 uppercase"
                    >
                        {{ __('capell-theme-education::generic.enrolment_step_two') }}
                    </span>
                    <span
                        class="bg-white/10 p-3 text-xs font-black text-white/70 uppercase"
                    >
                        {{ __('capell-theme-education::generic.enrolment_step_three') }}
                    </span>
                </div>

                @if (($section->actions ?? []) !== [])
                    <div class="mt-6 flex flex-wrap gap-3">
                        @foreach ($section->actions as $action)
                            <a
                                href="{{ $action['url'] }}"
                                class="{{ ($action['style'] ?? 'secondary') === 'primary' ? 'bg-white text-[#0f172a]' : 'border border-white/30 text-white' }} px-5 py-3 text-sm font-black"
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
