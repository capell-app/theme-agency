<section class="theme-section theme-section-cta bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div
            class="grid gap-8 bg-[#050b24] p-8 text-white md:grid-cols-[0.75fr_1fr] md:items-center md:p-10"
        >
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[#5eead4] uppercase"
                >
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
