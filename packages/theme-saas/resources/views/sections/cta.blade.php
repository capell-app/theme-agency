<section class="saas-final-cta bg-white">
    <div class="px-6">
        <div
            class="rounded-3xl bg-blue-600 p-2 text-white shadow-2xl shadow-blue-950/20"
        >
            <div
                class="grid gap-8 rounded-[1.25rem] border border-white/15 bg-[radial-gradient(circle_at_top_right,rgba(103,232,249,0.28),transparent_32%),linear-gradient(135deg,#2563eb,#1d4ed8)] p-8 md:grid-cols-[1fr_0.9fr] md:items-center md:p-12"
            >
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-cyan-100 uppercase"
                    >
                        {{ __('capell-theme-saas::generic.conversion_command_label') }}
                    </p>
                    <h2
                        class="mt-4 text-3xl font-black text-white sm:text-4xl md:text-5xl"
                    >
                        {{ $section->heading }}
                    </h2>
                    @if ($section->summary)
                        <p class="mt-4 max-w-2xl text-blue-50">
                            {{ $section->summary }}
                        </p>
                    @endif
                </div>

                <div
                    class="rounded-2xl border border-white/15 bg-slate-950/90 p-5 shadow-xl"
                >
                    <div class="grid gap-3" aria-hidden="true">
                        <span
                            class="text-xs font-black tracking-[0.18em] text-cyan-200 uppercase"
                        >
                            {{ __('capell-theme-saas::generic.pipeline_signal') }}
                        </span>
                        <span class="h-2 rounded-full bg-white/70"></span>
                        <span class="h-2 w-3/4 rounded-full bg-white/30"></span>
                        <span class="h-2 w-1/2 rounded-full bg-cyan-300"></span>
                    </div>

                    <div class="mt-6 flex flex-wrap gap-3">
                        @foreach ($section->actions as $action)
                            <a
                                href="{{ $action['url'] }}"
                                class="saas-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/30 text-white' : 'bg-white text-blue-700' }}"
                            >
                                {{ $action['label'] }}
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
