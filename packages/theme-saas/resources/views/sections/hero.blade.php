<section class="saas-hero relative overflow-hidden">
    <div
        class="relative grid items-center gap-12 px-6 lg:grid-cols-[1.02fr_0.98fr]"
    >
        <div>
            @if ($section->eyebrow)
                <p
                    class="mb-5 inline-flex rounded-full border border-cyan-200 bg-cyan-50 px-3 py-1 text-xs font-black tracking-widest text-cyan-700 uppercase"
                >
                    {{ $section->eyebrow }}
                </p>
            @endif

            <h1
                class="max-w-2xl text-4xl font-black tracking-tight text-slate-950 lg:text-5xl"
            >
                {{ $section->heading }}
            </h1>

            @if ($section->summary)
                <p class="mt-7 max-w-2xl text-xl">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-9 flex flex-wrap gap-3">
                @foreach ($section->actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="saas-cta {{ ($action['style'] ?? 'primary') === 'secondary' ? 'saas-cta-secondary' : 'saas-cta-primary' }}"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="saas-product-console p-3">
            <div
                class="mb-3 flex items-center justify-between gap-3 rounded-lg border border-white/10 bg-white/[0.04] px-3 py-2"
            >
                <span
                    class="text-xs font-black tracking-[0.18em] text-cyan-200 uppercase"
                >
                    {{ __('capell-theme-saas::generic.live_workspace_label') }}
                </span>
                <span
                    class="flex gap-1.5"
                    aria-hidden="true"
                >
                    <span class="size-2 rounded-full bg-emerald-300"></span>
                    <span class="size-2 rounded-full bg-cyan-300"></span>
                    <span class="size-2 rounded-full bg-amber-300"></span>
                </span>
            </div>

            @if ($section->mediaUrl)
                <div class="relative overflow-hidden rounded-lg">
                    <img
                        src="{{ $section->mediaUrl }}"
                        alt="{{ $section->mediaAlt ?? '' }}"
                        class="aspect-[4/3] w-full object-cover"
                    />
                    <div
                        class="absolute inset-x-4 bottom-4 grid gap-2 rounded-lg border border-white/20 bg-slate-950/88 p-4 text-white shadow-xl backdrop-blur sm:grid-cols-3"
                    >
                        <span>
                            <span
                                class="block text-[0.65rem] font-black tracking-widest text-cyan-200 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.activation_label') }}
                            </span>
                            <span class="mt-1 block text-lg font-black">
                                84%
                            </span>
                        </span>
                        <span>
                            <span
                                class="block text-[0.65rem] font-black tracking-widest text-emerald-200 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.retention_label') }}
                            </span>
                            <span class="mt-1 block text-lg font-black">
                                41%
                            </span>
                        </span>
                        <span>
                            <span
                                class="block text-[0.65rem] font-black tracking-widest text-amber-200 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.risk_label') }}
                            </span>
                            <span class="mt-1 block text-lg font-black">
                                {{ __('capell-theme-saas::generic.risk_low_label') }}
                            </span>
                        </span>
                    </div>
                </div>
            @else
                <div
                    class="saas-console-board aspect-[4/3] rounded-lg p-5 text-white"
                >
                    <div
                        class="grid h-full grid-rows-[auto_1fr_auto] gap-5 rounded-lg border border-white/10 bg-white/[0.04] p-5"
                    >
                        <div class="flex items-center justify-between">
                            <span class="grid gap-1.5">
                                <span
                                    class="text-xs font-black tracking-widest text-cyan-200 uppercase"
                                >
                                    {{ __('capell-theme-saas::generic.activation_map_label') }}
                                </span>
                                <span
                                    class="h-2 w-28 rounded-full bg-cyan-300"
                                ></span>
                            </span>
                            <span
                                class="rounded-full border border-emerald-300/40 bg-emerald-300/15 px-2.5 py-1 text-[0.65rem] font-black tracking-widest text-emerald-100 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.live_label') }}
                            </span>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-[0.7fr_1fr]">
                            <div class="grid gap-2">
                                <span
                                    class="rounded-lg border border-white/10 bg-white/10 p-3"
                                >
                                    <span
                                        class="block h-2 w-16 rounded-full bg-cyan-300"
                                    ></span>
                                    <span
                                        class="mt-5 block h-10 rounded-md bg-white/10"
                                    ></span>
                                </span>
                                <span
                                    class="rounded-lg border border-white/10 bg-emerald-300/10 p-3"
                                >
                                    <span
                                        class="block h-2 w-20 rounded-full bg-emerald-300"
                                    ></span>
                                    <span
                                        class="mt-4 block h-2 w-2/3 rounded-full bg-white/30"
                                    ></span>
                                </span>
                            </div>
                            <div
                                class="grid content-end gap-2 rounded-lg border border-white/10 bg-white/[0.05] p-3"
                            >
                                <span
                                    class="h-24 rounded-md bg-[linear-gradient(135deg,rgba(34,211,238,0.28),rgba(37,99,235,0.16),rgba(251,191,36,0.16))]"
                                ></span>
                                <span
                                    class="h-2.5 w-3/4 rounded-full bg-white/45"
                                ></span>
                                <span
                                    class="h-2.5 w-1/2 rounded-full bg-white/25"
                                ></span>
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <span
                                class="rounded-lg bg-cyan-400/25 p-3 text-[0.65rem] font-black text-cyan-100 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.trial_step_label') }}
                            </span>
                            <span
                                class="rounded-lg bg-blue-400/25 p-3 text-[0.65rem] font-black text-blue-100 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.aha_step_label') }}
                            </span>
                            <span
                                class="rounded-lg bg-amber-300/15 p-3 text-[0.65rem] font-black text-amber-100 uppercase"
                            >
                                {{ __('capell-theme-saas::generic.expansion_step_label') }}
                            </span>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
