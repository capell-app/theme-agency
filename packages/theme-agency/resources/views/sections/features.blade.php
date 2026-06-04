<section class="theme-features mx-auto max-w-7xl px-6 py-20">
    <div class="grid gap-6 lg:grid-cols-[0.75fr_1.25fr] lg:items-end">
        <div>
            <p
                class="text-sm font-black tracking-[0.24em] text-[var(--theme-primary)] uppercase"
            >
                {{ __('capell-theme-agency::generic.campaign_system') }}
            </p>
            <h2 class="mt-4 max-w-3xl text-5xl font-black tracking-tight">
                {{ $section->heading }}
            </h2>
        </div>
        @if ($section->summary)
            <p class="max-w-2xl text-zinc-400 lg:justify-self-end">
                {{ $section->summary }}
            </p>
        @endif
    </div>

    <div class="mt-10 grid gap-5 md:grid-cols-3">
        @foreach ($section->features as $feature)
            <article
                class="group overflow-hidden rounded-[1.5rem] border border-white/10 bg-white text-zinc-950 shadow-sm transition hover:-translate-y-1 hover:shadow-2xl"
            >
                <div class="site-brand-gradient p-4">
                    <div class="rounded-2xl bg-zinc-950/90 p-4 text-white">
                        <div class="flex items-center justify-between">
                            <span
                                class="text-xs font-black tracking-[0.18em] uppercase"
                            >
                                {{ __('capell-theme-agency::generic.scene_signal') }}
                                {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span
                                class="rounded-full bg-white px-3 py-1 text-xs font-black text-zinc-950"
                            >
                                {{ __('capell-theme-agency::generic.live_signal') }}
                            </span>
                        </div>
                        <div
                            class="mt-5 grid grid-cols-[1.2fr_0.8fr] gap-3"
                            aria-hidden="true"
                        >
                            <span class="h-16 rounded-xl bg-white/15"></span>
                            <span class="h-16 rounded-xl bg-white/25"></span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <p
                        class="text-sm font-bold tracking-widest text-[var(--theme-primary)] uppercase"
                    >
                        {{ $feature['icon'] ?? $feature['type'] ?? __('capell-theme-agency::generic.studio_signal') }}
                    </p>
                    <h3 class="mt-5 text-xl font-bold">
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-zinc-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-black">
                        <span
                            class="rounded-full bg-zinc-950 px-3 py-1 text-white"
                        >
                            {{ __('capell-theme-agency::generic.concept_signal') }}
                        </span>
                        <span
                            class="rounded-full bg-zinc-100 px-3 py-1 text-zinc-700"
                        >
                            {{ __('capell-theme-agency::generic.launch_signal') }}
                        </span>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
</section>
