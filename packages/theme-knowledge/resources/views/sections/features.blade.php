@php
    $features = $section->features ?? $section->items ?? [];
@endphp

<section
    class="theme-section theme-section-features bg-[var(--site-theme-surface)]"
>
    <div class="mx-auto max-w-5xl px-6 py-14">
        <div class="grid gap-6 lg:grid-cols-[0.72fr_1fr] lg:items-end">
            <div>
                <p
                    class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
                >
                    {{ __('capell-theme-knowledge::generic.research_paths_label') }}
                </p>
                <h2
                    class="mt-4 text-4xl font-black tracking-tight text-[var(--site-theme-foreground)]"
                >
                    {{ $heading ?? $section->heading }}
                </h2>
            </div>

            @if (($summary ?? $section->summary ?? null) !== null)
                <p
                    class="max-w-2xl text-lg leading-8 text-slate-600 lg:justify-self-end"
                >
                    {{ $summary ?? $section->summary }}
                </p>
            @endif
        </div>

        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ($features as $index => $feature)
                <article
                    class="group border border-[var(--site-theme-primary-soft)] bg-white p-5 shadow-sm transition hover:-translate-y-1 hover:border-[var(--site-theme-primary)] hover:shadow-xl"
                >
                    <div
                        class="mb-5 border border-[var(--site-theme-primary-border)] bg-[var(--site-theme-primary-panel)] p-4"
                    >
                        <div class="flex items-center justify-between">
                            <span
                                class="font-mono text-xs font-black text-[var(--site-theme-primary)]"
                            >
                                {{ __('capell-theme-knowledge::generic.index_signal') }}
                                {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}
                            </span>
                            <span
                                class="bg-[var(--site-theme-accent-panel)] px-3 py-1 text-xs font-black text-[var(--site-theme-accent-contrast)]"
                            >
                                {{ __('capell-theme-knowledge::generic.reading_signal') }}
                            </span>
                        </div>
                        <div
                            class="mt-4 space-y-2"
                            aria-hidden="true"
                        >
                            <span
                                class="block h-2 w-3/4 bg-[var(--site-theme-primary-contrast)]"
                            ></span>
                            <span class="block h-2 w-full bg-white"></span>
                            <span class="block h-2 w-5/6 bg-white"></span>
                            <span
                                class="block h-2 w-2/3 bg-[var(--site-theme-primary-soft)]"
                            ></span>
                        </div>
                    </div>

                    <p
                        class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-primary)] uppercase"
                    >
                        {{ $feature['type'] ?? __('capell-theme-knowledge::generic.guide_signal') }}
                    </p>
                    <h3
                        class="mt-3 text-lg font-black text-[var(--site-theme-foreground)]"
                    >
                        {{ $feature['title'] }}
                    </h3>
                    <p class="mt-3 text-sm leading-6 text-slate-600">
                        {{ $feature['description'] ?? $feature['summary'] ?? '' }}
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold">
                        <span
                            class="bg-[var(--site-theme-primary-border)] px-3 py-1 text-[var(--site-theme-primary-strong)]"
                        >
                            {{ __('capell-theme-knowledge::generic.topic_signal') }}
                        </span>
                        <span
                            class="bg-[var(--site-theme-accent-panel)] px-3 py-1 text-[var(--site-theme-accent-contrast)]"
                        >
                            {{ __('capell-theme-knowledge::generic.review_signal') }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>
