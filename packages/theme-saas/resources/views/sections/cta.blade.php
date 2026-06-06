<section class="saas-final-cta bg-white">
    <div class="px-6">
        <div class="saas-final-cta__frame rounded-3xl p-2 text-white shadow-2xl shadow-blue-950/20">
            <div
                class="saas-final-cta__panel grid gap-8 rounded-[1.25rem] border border-white/15 p-8 md:grid-cols-[1fr_0.9fr] md:items-center md:p-12"
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

                <div class="saas-command-panel p-5 shadow-xl">
                    <div
                        class="grid gap-3"
                        aria-hidden="true"
                    >
                        <span
                            class="text-xs font-black tracking-[0.18em] text-cyan-200 uppercase"
                        >
                            {{ __('capell-theme-saas::generic.pipeline_signal') }}
                        </span>
                        <span class="h-2 rounded-full bg-white/70"></span>
                        <span class="h-2 w-3/4 rounded-full bg-white/30"></span>
                        <span class="h-2 w-1/2 rounded-full bg-cyan-300"></span>
                    </div>

                    <div
                        class="mt-5 grid grid-cols-3 gap-2 text-center text-[0.65rem] font-black uppercase"
                    >
                        <span
                            class="saas-pipeline-stage saas-pipeline-stage--trial rounded-md px-2 py-2"
                        >
                            {{ __('capell-theme-saas::generic.trial_step_label') }}
                        </span>
                        <span
                            class="saas-pipeline-stage saas-pipeline-stage--activation rounded-md px-2 py-2"
                        >
                            {{ __('capell-theme-saas::generic.activation_label') }}
                        </span>
                        <span
                            class="saas-pipeline-stage saas-pipeline-stage--expansion rounded-md px-2 py-2"
                        >
                            {{ __('capell-theme-saas::generic.expansion_step_label') }}
                        </span>
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
