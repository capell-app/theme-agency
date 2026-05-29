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
                    <figure
                        class="{{ $loop->first ? 'sm:col-span-2' : '' }} overflow-hidden rounded-2xl border border-white/10 bg-white/[0.04] shadow-2xl shadow-black/20"
                    >
                        <div
                            class="grid min-h-full gap-0 md:grid-cols-[0.9fr_1.1fr]"
                        >
                            <div
                                class="flex min-h-36 items-end bg-[#071225] p-5"
                                aria-hidden="true"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-center justify-between gap-3"
                                    >
                                        <span
                                            class="h-2 w-20 rounded-full bg-cyan-300"
                                        ></span>
                                        <span
                                            class="h-2 w-10 rounded-full bg-blue-500"
                                        ></span>
                                    </div>
                                    <div class="mt-8 space-y-2">
                                        <span
                                            class="block h-2 rounded-full bg-white/50"
                                        ></span>
                                        <span
                                            class="block h-2 w-2/3 rounded-full bg-white/25"
                                        ></span>
                                    </div>
                                    <div class="mt-5 grid grid-cols-3 gap-2">
                                        <span
                                            class="h-8 rounded-md bg-cyan-400/30"
                                        ></span>
                                        <span
                                            class="h-8 rounded-md bg-blue-500/40"
                                        ></span>
                                        <span
                                            class="h-8 rounded-md bg-white/10"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <figcaption class="p-5 sm:p-6">
                                <blockquote
                                    class="text-3xl font-black text-white"
                                >
                                    {{ $item['metric'] ?? $item['quote'] ?? '' }}
                                </blockquote>
                                @if ($item['summary'] ?? null)
                                    <p
                                        class="mt-3 text-sm leading-6 text-slate-300"
                                    >
                                        {{ $item['summary'] }}
                                    </p>
                                @endif

                                <p
                                    class="mt-5 border-t border-white/10 pt-4 text-xs font-black tracking-widest text-cyan-300 uppercase"
                                >
                                    {{ $item['name'] ?? $item['logo'] ?? __('capell-theme-saas::generic.outcome_signal') }}
                                </p>
                                @if ($item['role'] ?? null)
                                    <p class="mt-1 text-sm text-slate-400">
                                        {{ $item['role'] }}
                                    </p>
                                @endif
                            </figcaption>
                        </div>
                    </figure>
                @endforeach
            </div>
        </div>
    </div>
</section>
