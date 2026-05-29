<section class="theme-section theme-section-cta bg-white">
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div
            class="relative overflow-hidden bg-[#052e16] p-8 text-white md:p-10"
        >
            <div
                class="absolute -top-10 -right-10 h-36 w-36 rounded-full border-[28px] border-[#facc15]"
                aria-hidden="true"
            ></div>
            <div
                class="relative grid gap-8 md:grid-cols-[0.72fr_1fr] md:items-center"
            >
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[#fde047] uppercase"
                    >
                        {{ __('capell-theme-nonprofit::generic.supporter_cta_label') }}
                    </p>
                    <h2 class="mt-4 text-4xl font-black tracking-tight">
                        {{ $section->heading }}
                    </h2>
                </div>

                <div>
                    <p class="max-w-2xl text-base leading-7 text-emerald-100">
                        {{ $section->summary ?? __('capell-theme-nonprofit::generic.cta_copy') }}
                    </p>

                    @if (($section->actions ?? []) !== [])
                        <div class="mt-6 flex flex-wrap gap-3">
                            @foreach ($section->actions as $action)
                                <a
                                    href="{{ $action['url'] }}"
                                    class="{{ ($action['style'] ?? 'secondary') === 'primary' ? 'bg-white text-[#052e16]' : 'border border-white/30 text-white' }} px-5 py-3 text-sm font-black"
                                >
                                    {{ $action['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
