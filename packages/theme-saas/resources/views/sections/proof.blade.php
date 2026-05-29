<section class="saas-proof border-y border-slate-200 bg-slate-950 text-white">
    <div class="px-6">
        <div class="grid gap-8 lg:grid-cols-[0.72fr_1.28fr] lg:items-start">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-cyan-300 uppercase"
                >
                    {{ __('capell-theme-saas::generic.growth_ledger_label') }}
                </p>
                <h2
                    class="mt-4 max-w-2xl text-4xl font-black tracking-tight text-white"
                >
                    {{ $section->heading }}
                </h2>
                @if ($section->summary)
                    <p class="mt-4 max-w-xl text-slate-300">
                        {{ $section->summary }}
                    </p>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                @foreach ($section->items as $item)
                    <article
                        class="{{ $loop->first ? 'sm:col-span-2' : '' }} overflow-hidden rounded-2xl border border-white/10 bg-[#071225] shadow-2xl shadow-black/20"
                    >
                        <div
                            class="{{ $loop->first ? 'md:grid-cols-[0.78fr_0.92fr_0.58fr]' : 'md:grid-cols-[0.78fr_1fr]' }} grid min-h-full gap-0"
                        >
                            <div
                                class="flex min-h-36 items-end bg-slate-900 p-5"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="text-xs font-black text-cyan-200 uppercase"
                                        >
                                            {{ __('capell-theme-saas::generic.activation_stage_label') }}
                                        </span>
                                        <span
                                            class="rounded-full bg-blue-500 px-2 py-1 text-xs font-black text-white"
                                        >
                                            {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </div>
                                    <div
                                        class="mt-8 grid grid-cols-3 gap-2 text-center text-[0.65rem] font-black uppercase"
                                    >
                                        <span
                                            class="rounded-md bg-cyan-300 px-2 py-3 text-slate-950"
                                        >
                                            {{ __('capell-theme-saas::generic.trial_step_label') }}
                                        </span>
                                        <span
                                            class="rounded-md bg-blue-500 px-2 py-3 text-white"
                                        >
                                            {{ __('capell-theme-saas::generic.aha_step_label') }}
                                        </span>
                                        <span
                                            class="rounded-md bg-white/10 px-2 py-3 text-slate-200"
                                        >
                                            {{ __('capell-theme-saas::generic.expansion_step_label') }}
                                        </span>
                                    </div>
                                    <div
                                        class="mt-4 h-2 rounded-full bg-white/10"
                                    >
                                        <span
                                            class="block h-2 w-3/4 rounded-full bg-cyan-300"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div class="p-5 sm:p-6">
                                <p
                                    class="text-xs font-black text-cyan-300 uppercase"
                                >
                                    {{ $item['name'] ?? $item['logo'] ?? __('capell-theme-saas::generic.outcome_signal') }}
                                </p>
                                <p
                                    class="mt-4 text-5xl leading-none font-black text-white"
                                >
                                    {{ $item['metric'] ?? $item['quote'] ?? '' }}
                                </p>
                                @if ($item['summary'] ?? null)
                                    <p
                                        class="mt-3 text-sm leading-6 text-slate-300"
                                    >
                                        {{ $item['summary'] }}
                                    </p>
                                @endif

                                @if ($item['role'] ?? null)
                                    <p class="mt-3 text-sm text-slate-400">
                                        {{ $item['role'] }}
                                    </p>
                                @endif
                            </div>

                            @if ($loop->first)
                                <div
                                    class="border-t border-white/10 bg-white/[0.04] p-5 md:border-t-0 md:border-l"
                                >
                                    <div class="grid gap-3">
                                        <div
                                            class="rounded-lg border border-white/10 p-3"
                                        >
                                            <p
                                                class="text-xs font-black text-slate-400 uppercase"
                                            >
                                                {{ __('capell-theme-saas::generic.cohort_label') }}
                                            </p>
                                            <span
                                                class="mt-3 block h-2 rounded-full bg-cyan-300"
                                            ></span>
                                        </div>
                                        <div
                                            class="rounded-lg border border-white/10 p-3"
                                        >
                                            <p
                                                class="text-xs font-black text-slate-400 uppercase"
                                            >
                                                {{ __('capell-theme-saas::generic.event_label') }}
                                            </p>
                                            <span
                                                class="mt-3 block h-2 rounded-full bg-blue-500"
                                            ></span>
                                        </div>
                                        <div
                                            class="rounded-lg border border-white/10 p-3"
                                        >
                                            <p
                                                class="text-xs font-black text-slate-400 uppercase"
                                            >
                                                {{ __('capell-theme-saas::generic.health_label') }}
                                            </p>
                                            <span
                                                class="mt-3 block h-2 rounded-full bg-emerald-300"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
