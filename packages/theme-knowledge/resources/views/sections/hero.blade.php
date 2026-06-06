@php
    $heading = $section->heading ?? ($heading ?? null);
    $summary = $section->summary ?? ($summary ?? __('capell-theme-knowledge::generic.hero_summary'));
    $eyebrow = $section->eyebrow ?? ($eyebrow ?? __('capell-theme-knowledge::generic.hero_label'));
    $actions = $section->actions ?? ($actions ?? []);
    $primaryAction = $actions[0] ?? [
        'label' => __('capell-theme-knowledge::generic.hero_primary_action'),
        'url' => '#library',
    ];
    $secondaryAction = $actions[1] ?? [
        'label' => __('capell-theme-knowledge::generic.hero_secondary_action'),
        'url' => '#digest',
    ];
    $imageUrl = $section->mediaUrl ?? ($imageUrl ?? ($image ?? null));
    $imageAlt = $section->mediaAlt ?? ($imageAlt ?? '');
@endphp

<section class="theme-section theme-section-hero knowledge-hero bg-[var(--site-theme-ink)]">
    @isset($heading)
        <div
            class="knowledge-hero-grid mx-auto grid max-w-6xl gap-8 px-6 py-16 lg:grid-cols-[0.88fr_1.12fr] lg:items-center lg:py-20"
        >
            <div class="space-y-5">
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-accent)] uppercase"
                >
                    {{ $eyebrow }}
                </p>
                <h2
                    class="max-w-3xl text-5xl leading-tight font-black tracking-normal text-white"
                >
                    {{ $heading }}
                </h2>
                @if ($summary)
                    <p class="max-w-2xl text-lg leading-8 text-slate-300">
                        {{ $summary }}
                    </p>
                @endif

                <div class="flex flex-wrap gap-3">
                    <a
                        href="{{ $primaryAction['url'] ?? '#library' }}"
                        class="inline-flex bg-[var(--site-theme-accent)] px-5 py-3 text-sm font-black text-[var(--site-theme-ink)]"
                    >
                        {{ $primaryAction['label'] ?? __('capell-theme-knowledge::generic.hero_primary_action') }}
                    </a>
                    <a
                        href="{{ $secondaryAction['url'] ?? '#digest' }}"
                        class="inline-flex border border-white/20 bg-white/5 px-5 py-3 text-sm font-black text-white"
                    >
                        {{ $secondaryAction['label'] ?? __('capell-theme-knowledge::generic.hero_secondary_action') }}
                    </a>
                </div>

                <div class="grid gap-3 sm:grid-cols-3">
                    <div class="border border-white/10 bg-white/5 p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[var(--site-theme-primary-muted)] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.hero_metric_guides_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-white">
                            {{ __('capell-theme-knowledge::generic.hero_metric_guides_value') }}
                        </p>
                    </div>
                    <div class="border border-white/10 bg-white/5 p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[var(--site-theme-primary-muted)] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.hero_metric_topics_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-white">
                            {{ __('capell-theme-knowledge::generic.hero_metric_topics_value') }}
                        </p>
                    </div>
                    <div class="border border-[var(--site-theme-accent)]/40 bg-[var(--site-theme-accent)]/10 p-4">
                        <p
                            class="text-xs font-black tracking-[0.15em] text-[var(--site-theme-accent-strong)] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.hero_metric_saved_label') }}
                        </p>
                        <p class="mt-2 text-3xl font-black text-white">
                            {{ __('capell-theme-knowledge::generic.hero_metric_saved_value') }}
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="knowledge-command-panel border border-white/10 bg-[var(--site-theme-ink-panel)] p-3 shadow-2xl shadow-black/30"
            >
                <div class="border border-white/10 bg-[var(--site-theme-ink-panel-raised)] p-4">
                    <div class="grid gap-4 lg:grid-cols-[1fr_0.75fr]">
                        <div class="bg-[var(--site-theme-primary-canvas)] p-4">
                            @if ($imageUrl)
                                <img
                                    src="{{ $imageUrl }}"
                                    alt="{{ $imageAlt }}"
                                    class="aspect-[16/10] w-full object-cover"
                                />
                            @else
                                <div
                                    class="aspect-[16/10] bg-[var(--site-theme-ink-accent)] p-5"
                                    aria-hidden="true"
                                >
                                    <div class="grid h-full content-end gap-4">
                                        <span
                                            class="h-4 w-24 bg-[var(--site-theme-accent)]"
                                        ></span>
                                        <span
                                            class="h-3 w-3/4 bg-white/60"
                                        ></span>
                                        <span
                                            class="h-3 w-1/2 bg-white/30"
                                        ></span>
                                        <div class="grid grid-cols-3 gap-3">
                                            <span
                                                class="h-12 bg-white/15"
                                            ></span>
                                            <span
                                                class="h-12 bg-[var(--site-theme-primary)]"
                                            ></span>
                                            <span
                                                class="h-12 bg-white/15"
                                            ></span>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <div class="mt-4 grid gap-3 sm:grid-cols-3">
                                <span
                                    class="border border-[var(--site-theme-primary-soft)] bg-white px-3 py-2 text-xs font-black tracking-[0.14em] text-[var(--site-theme-primary)] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.cta_step_read') }}
                                </span>
                                <span
                                    class="border border-[var(--site-theme-primary-soft)] bg-white px-3 py-2 text-xs font-black tracking-[0.14em] text-[var(--site-theme-primary)] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.cta_step_save') }}
                                </span>
                                <span
                                    class="border border-[var(--site-theme-primary-soft)] bg-white px-3 py-2 text-xs font-black tracking-[0.14em] text-[var(--site-theme-primary)] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.cta_step_share') }}
                                </span>
                            </div>
                        </div>

                        <div class="grid gap-3">
                            <div
                                class="border border-white/10 bg-[var(--site-theme-ink)] p-4"
                            >
                                <p
                                    class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary-muted)] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_search_label') }}
                                </p>
                                <div
                                    class="mt-4 border border-white/10 bg-white/5 p-3"
                                >
                                    <div class="h-3 w-3/4 bg-[var(--site-theme-code-accent)]"></div>
                                    <div
                                        class="mt-3 grid grid-cols-[1fr_auto] gap-3"
                                    >
                                        <span
                                            class="h-8 border border-white/10 bg-white/10"
                                        ></span>
                                        <span
                                            class="h-8 w-16 bg-[var(--site-theme-accent)]"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="border border-[var(--site-theme-accent)]/40 bg-[var(--site-theme-accent)]/10 p-4"
                            >
                                <p
                                    class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-accent-strong)] uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_queue_label') }}
                                </p>
                                <div class="mt-4 grid gap-2">
                                    <div
                                        class="grid grid-cols-[auto_1fr] gap-3"
                                    >
                                        <span class="font-black text-[var(--site-theme-accent)]">
                                            01
                                        </span>
                                        <span
                                            class="h-3 self-center bg-[var(--site-theme-primary)]"
                                        ></span>
                                    </div>
                                    <div
                                        class="grid grid-cols-[auto_1fr] gap-3"
                                    >
                                        <span class="font-black text-[var(--site-theme-accent)]">
                                            02
                                        </span>
                                        <span
                                            class="h-3 self-center bg-[var(--site-theme-primary-muted)]"
                                        ></span>
                                    </div>
                                    <div
                                        class="grid grid-cols-[auto_1fr] gap-3"
                                    >
                                        <span class="font-black text-[var(--site-theme-accent)]">
                                            03
                                        </span>
                                        <span
                                            class="h-3 self-center bg-[var(--site-theme-primary-soft)]"
                                        ></span>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-white/10 bg-white/5 p-4">
                                <p
                                    class="text-xs font-black tracking-[0.16em] text-slate-400 uppercase"
                                >
                                    {{ __('capell-theme-knowledge::generic.hero_review_label') }}
                                </p>
                                <p class="mt-2 text-2xl font-black text-white">
                                    {{ __('capell-theme-knowledge::generic.hero_review_value') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endisset
</section>
