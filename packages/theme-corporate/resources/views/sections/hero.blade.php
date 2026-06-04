@php
    $actions = ($section->actions ?? []) !== []
        ? $section->actions
        : [
            ['label' => __('capell-theme-corporate::generic.hero_primary_action'), 'url' => '#content', 'style' => 'primary'],
            ['label' => __('capell-theme-corporate::generic.hero_secondary_action'), 'url' => '#gallery', 'style' => 'secondary'],
        ];
    $stats = $section->stats ?? [
        [
            'label' => __('capell-theme-corporate::generic.hero_stat_confidence_label'),
            'value' => __('capell-theme-corporate::generic.hero_stat_confidence_value'),
        ],
        [
            'label' => __('capell-theme-corporate::generic.hero_stat_advisory_label'),
            'value' => __('capell-theme-corporate::generic.hero_stat_advisory_value'),
        ],
        [
            'label' => __('capell-theme-corporate::generic.hero_stat_reporting_label'),
            'value' => __('capell-theme-corporate::generic.hero_stat_reporting_value'),
        ],
    ];
@endphp

<section
    class="theme-hero border-b border-slate-200/80 bg-[#f7f8f6] dark:border-white/10 dark:bg-slate-950"
>
    <div
        class="mx-auto grid max-w-7xl items-end gap-5 px-4 py-6 sm:px-6 sm:py-10 md:py-14 lg:grid-cols-[0.88fr_1.12fr] lg:gap-10 lg:py-18"
    >
        <div class="space-y-4 sm:space-y-5">
            <div
                class="flex flex-wrap items-center gap-2 text-[0.68rem] font-semibold tracking-[0.16em] text-slate-500 uppercase dark:text-slate-400"
            >
                <span
                    class="text-[var(--theme-primary)] dark:text-[var(--theme-accent)]"
                >
                    {{ $section->eyebrow ?: __('capell-theme-corporate::generic.hero_default_eyebrow') }}
                </span>

                @if ($section->mediaAlt)
                    <span class="h-px w-6 bg-slate-300 dark:bg-white/15"></span>
                    <span>{{ $section->mediaAlt }}</span>
                @endif
            </div>

            <h1
                class="max-w-4xl text-4xl leading-none font-semibold text-slate-950 sm:text-5xl lg:text-7xl dark:text-white"
            >
                {{ $section->heading }}
            </h1>

            @if ($section->summary)
                <p
                    class="max-w-xl text-sm leading-6 text-slate-600 sm:text-base sm:leading-7 lg:text-lg lg:leading-8 dark:text-slate-300"
                >
                    {{ $section->summary }}
                </p>
            @endif

            <div class="flex flex-wrap gap-2 sm:gap-3">
                @foreach ($actions as $action)
                    <a
                        href="{{ $action['url'] }}"
                        class="{{ ($action['style'] ?? 'primary') === 'secondary' ? 'border border-slate-300 text-slate-800 hover:border-slate-950 dark:border-white/15 dark:text-slate-200 dark:hover:border-white' : 'bg-[var(--theme-accent)] text-slate-950 hover:bg-white' }} rounded-full px-3.5 py-2 text-xs font-semibold transition sm:px-5 sm:py-3 sm:text-sm"
                    >
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>

            <dl
                class="grid grid-cols-3 gap-2 border-t border-slate-200 pt-4 text-xs sm:max-w-xl sm:gap-4 sm:pt-5 dark:border-white/10"
            >
                @foreach ($stats as $stat)
                    @php
                        $statLabel = is_array($stat) && is_scalar($stat['label'] ?? null) ? (string) $stat['label'] : '';
                        $statValue = is_array($stat) && is_scalar($stat['value'] ?? null) ? (string) $stat['value'] : '';
                    @endphp

                    @if ($statLabel !== '' && $statValue !== '')
                        <div>
                            <dt
                                class="font-semibold tracking-[0.12em] text-slate-400 uppercase dark:text-slate-500"
                            >
                                {{ $statLabel }}
                            </dt>
                            <dd
                                class="mt-1 font-semibold text-slate-900 dark:text-white"
                            >
                                {{ $statValue }}
                            </dd>
                        </div>
                    @endif
                @endforeach
            </dl>
        </div>

        <figure class="relative">
            <div
                class="border border-slate-200 bg-white p-3 shadow-xl shadow-slate-950/5 dark:border-white/10 dark:bg-white/[0.04]"
            >
                @if ($section->mediaUrl)
                    <img
                        src="{{ $section->mediaUrl }}"
                        alt="{{ $section->mediaAlt ?? '' }}"
                        class="aspect-[16/10] max-h-[18rem] w-full rounded-[0.35rem] object-cover sm:aspect-[5/4] sm:max-h-none"
                    />
                @else
                    <div
                        class="grid aspect-[16/10] max-h-[18rem] gap-3 bg-slate-950 p-4 text-white sm:aspect-[5/4] sm:max-h-none lg:grid-cols-[1fr_0.7fr]"
                    >
                        <div
                            class="flex min-h-full flex-col justify-between border border-white/10 bg-white/[0.04] p-4"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <p
                                    class="text-xs font-semibold tracking-[0.16em] text-[var(--theme-accent)] uppercase"
                                >
                                    {{ __('capell-theme-corporate::generic.board_briefing_label') }}
                                </p>
                                <p class="font-mono text-xs text-white/45">
                                    01
                                </p>
                            </div>

                            <div
                                class="space-y-3"
                                aria-hidden="true"
                            >
                                <span
                                    class="block h-2 w-24 bg-[var(--theme-accent)]"
                                ></span>
                                <span
                                    class="block h-3 w-4/5 bg-white/35"
                                ></span>
                                <span
                                    class="block h-3 w-3/5 bg-white/20"
                                ></span>
                                <span class="mt-5 grid grid-cols-3 gap-2">
                                    <span class="h-12 bg-white/12"></span>
                                    <span
                                        class="h-12 bg-[var(--theme-primary)]"
                                    ></span>
                                    <span class="h-12 bg-white/12"></span>
                                </span>
                            </div>

                            <div
                                class="grid grid-cols-3 gap-px bg-white/10 text-[0.65rem] font-semibold text-white/70 uppercase"
                            >
                                <span class="bg-slate-950 px-2 py-2">
                                    {{ __('capell-theme-corporate::generic.hero_agenda_label') }}
                                </span>
                                <span class="bg-slate-950 px-2 py-2">
                                    {{ __('capell-theme-corporate::generic.hero_assurance_label') }}
                                </span>
                                <span class="bg-slate-950 px-2 py-2">
                                    {{ __('capell-theme-corporate::generic.hero_decisions_label') }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <div
                                class="border border-white/10 bg-white p-4 text-slate-950"
                            >
                                <p
                                    class="text-xs font-semibold tracking-[0.16em] text-slate-500 uppercase"
                                >
                                    {{ __('capell-theme-corporate::generic.board_pack_label') }}
                                </p>
                                <p class="mt-2 text-lg font-semibold">
                                    {{ __('capell-theme-corporate::generic.hero_pack_ready') }}
                                </p>
                            </div>
                            <div
                                class="border border-white/10 bg-white/[0.06] p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-[0.16em] text-white/55 uppercase"
                                >
                                    {{ __('capell-theme-corporate::generic.decision_log_label') }}
                                </p>
                                <div
                                    class="mt-4 space-y-2"
                                    aria-hidden="true"
                                >
                                    <span
                                        class="block h-2 w-full bg-white/35"
                                    ></span>
                                    <span
                                        class="block h-2 w-4/5 bg-white/20"
                                    ></span>
                                    <span
                                        class="block h-2 w-3/5 bg-[var(--theme-accent)]"
                                    ></span>
                                </div>
                            </div>
                            <div
                                class="border border-white/10 bg-white/[0.06] p-4"
                            >
                                <p
                                    class="text-xs font-semibold tracking-[0.16em] text-white/55 uppercase"
                                >
                                    {{ __('capell-theme-corporate::generic.register_signal') }}
                                </p>
                                <p
                                    class="mt-2 font-mono text-2xl font-semibold"
                                >
                                    04
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <figcaption
                class="mt-2 flex items-center justify-between gap-3 text-[0.68rem] tracking-[0.14em] text-slate-500 uppercase sm:mt-3 sm:gap-4 sm:text-xs sm:tracking-[0.16em] dark:text-slate-400"
            >
                <span>
                    {{ $section->mediaAlt ?: __('capell-theme-corporate::generic.board_briefing_label') }}
                </span>
                <span class="h-px grow bg-slate-300 dark:bg-white/15"></span>
            </figcaption>
        </figure>
    </div>
</section>
