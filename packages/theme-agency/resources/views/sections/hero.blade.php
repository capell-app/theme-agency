<section class="theme-hero overflow-hidden bg-zinc-950 text-white">
    <div
        class="mx-auto grid max-w-7xl gap-10 px-6 py-20 lg:grid-cols-[0.86fr_1.14fr] lg:items-center lg:py-24"
    >
        <div>
            @if ($section->eyebrow)
                <p
                    class="mb-6 text-sm font-bold tracking-[0.25em] text-[var(--theme-accent)] uppercase"
                >
                    {{ $section->eyebrow }}
                </p>
            @endif

            <h1
                class="max-w-4xl text-6xl font-black tracking-tight md:text-7xl"
            >
                {{ $section->heading }}
            </h1>
            @if ($section->summary)
                <p class="mt-6 max-w-xl text-lg leading-8 text-white/85">
                    {{ $section->summary }}
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                @foreach ($section->actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-white/25 text-white' : 'bg-[var(--theme-primary)] text-white' }} rounded-full px-6 py-3 text-sm font-bold"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>

            <div
                class="mt-10 grid max-w-xl grid-cols-3 gap-3 text-xs font-black uppercase"
            >
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-white/75">
                        {{ __('capell-theme-agency::generic.hero_status_label') }}
                    </p>
                    <p class="mt-2 text-[var(--theme-accent)]">
                        {{ __('capell-theme-agency::generic.live_signal') }}
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-white/75">
                        {{ __('capell-theme-agency::generic.hero_channels_label') }}
                    </p>
                    <p class="mt-2 text-white">
                        {{ __('capell-theme-agency::generic.hero_channels_value') }}
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                    <p class="text-white/75">
                        {{ __('capell-theme-agency::generic.hero_calendar_label') }}
                    </p>
                    <p class="mt-2 text-white">
                        {{ __('capell-theme-agency::generic.hero_calendar_value') }}
                    </p>
                </div>
            </div>
        </div>

        <div
            class="site-brand-gradient rounded-[2rem] p-3 shadow-2xl shadow-fuchsia-950/30"
        >
            <div class="rounded-[1.55rem] bg-zinc-950 p-4">
                <div class="grid gap-4 lg:grid-cols-[1fr_0.58fr]">
                    <div class="rounded-[1.25rem] bg-white p-3">
                        @if ($section->mediaUrl)
                            <img
                                src="{{ $section->mediaUrl }}"
                                alt="{{ $section->mediaAlt ?? '' }}"
                                width="1200"
                                height="900"
                                loading="eager"
                                decoding="async"
                                fetchpriority="high"
                                class="aspect-[4/3] w-full rounded-[1rem] object-cover"
                            />
                        @else
                            <div
                                class="site-brand-gradient flex aspect-[4/3] items-end rounded-[1rem] p-5"
                                aria-hidden="true"
                            >
                                <div class="w-full">
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <span
                                            class="rounded-full bg-white px-3 py-1 text-[0.65rem] font-black text-zinc-950"
                                        >
                                            {{ __('capell-theme-agency::generic.hero_scene_label') }}
                                        </span>
                                        <span
                                            class="font-mono text-xs font-black text-white/75"
                                        >
                                            {{ __('capell-theme-agency::generic.hero_scene_value') }}
                                        </span>
                                    </div>
                                    <div class="mt-20 space-y-3">
                                        <span
                                            class="block h-3 w-3/4 rounded-full bg-white/55"
                                        ></span>
                                        <span
                                            class="block h-3 w-1/2 rounded-full bg-white/30"
                                        ></span>
                                    </div>
                                    <div class="mt-7 grid grid-cols-3 gap-3">
                                        <span
                                            class="h-14 rounded-2xl bg-white/20"
                                        ></span>
                                        <span
                                            class="h-14 rounded-2xl bg-[var(--theme-primary)]"
                                        ></span>
                                        <span
                                            class="h-14 rounded-2xl bg-white/15"
                                        ></span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="grid gap-4">
                        <div
                            class="rounded-[1.25rem] border border-white/10 bg-white/10 p-4"
                        >
                            <p
                                class="text-xs font-black tracking-[0.18em] text-white/75 uppercase"
                            >
                                {{ __('capell-theme-agency::generic.hero_asset_label') }}
                            </p>
                            <div
                                class="mt-4 grid grid-cols-3 gap-2"
                                aria-hidden="true"
                            >
                                <span
                                    class="h-10 rounded-xl bg-[var(--theme-primary)]"
                                ></span>
                                <span
                                    class="h-10 rounded-xl bg-white/25"
                                ></span>
                                <span
                                    class="h-10 rounded-xl bg-[var(--theme-accent)]"
                                ></span>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.25rem] border border-white/10 bg-white p-4 text-zinc-950"
                        >
                            <p
                                class="text-xs font-black tracking-[0.18em] text-zinc-500 uppercase"
                            >
                                {{ __('capell-theme-agency::generic.channel_signal') }}
                            </p>
                            <div
                                class="mt-4 space-y-3"
                                aria-hidden="true"
                            >
                                <span
                                    class="block h-3 w-full rounded-full bg-zinc-950"
                                ></span>
                                <span
                                    class="block h-3 w-4/5 rounded-full bg-[var(--theme-primary)]"
                                ></span>
                                <span
                                    class="block h-3 w-3/5 rounded-full bg-[var(--theme-accent)]"
                                ></span>
                            </div>
                        </div>

                        <div
                            class="rounded-[1.25rem] border border-white/10 bg-white/10 p-4"
                        >
                            <p
                                class="text-xs font-black tracking-[0.18em] text-white/75 uppercase"
                            >
                                {{ __('capell-theme-agency::generic.launch_room') }}
                            </p>
                            <p class="mt-2 text-2xl font-black text-white">
                                {{ __('capell-theme-agency::generic.live_signal') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
