@php
    $searchAvailable ??= false;
    $searchAction ??= '/search';
    $searchQuery = $query ?? '';

    $filters ??= [
        __('capell-theme-knowledge::generic.topic_hub_strategy'),
        __('capell-theme-knowledge::generic.topic_hub_design'),
        __('capell-theme-knowledge::generic.topic_hub_operations'),
        __('capell-theme-knowledge::generic.topic_hub_growth'),
    ];

    $searchItems = $items ?? [
        [
            'title' => __('capell-theme-knowledge::generic.search_result_guide'),
            'summary' => __('capell-theme-knowledge::generic.search_result_guide_summary'),
            'type' => __('capell-theme-knowledge::generic.guide_signal'),
            'score' => '94%',
            'meta' => [__('capell-theme-knowledge::generic.review_signal'), __('capell-theme-knowledge::generic.reading_signal')],
        ],
        [
            'title' => __('capell-theme-knowledge::generic.search_result_research'),
            'summary' => __('capell-theme-knowledge::generic.search_result_research_summary'),
            'type' => __('capell-theme-knowledge::generic.library_research'),
            'score' => '89%',
            'meta' => [__('capell-theme-knowledge::generic.proof_signal'), __('capell-theme-knowledge::generic.topic_signal')],
        ],
        [
            'title' => __('capell-theme-knowledge::generic.search_result_template'),
            'summary' => __('capell-theme-knowledge::generic.search_result_template_summary'),
            'type' => __('capell-theme-knowledge::generic.library_templates'),
            'score' => '84%',
            'meta' => [__('capell-theme-knowledge::generic.saved_signal'), __('capell-theme-knowledge::generic.library_signal')],
        ],
    ];
@endphp

<section
    class="theme-section theme-section-search-listing knowledge-search-console bg-[var(--site-theme-ink)]"
>
    <div class="mx-auto max-w-6xl px-6 py-14">
        @isset($heading)
            <div class="grid gap-5 lg:grid-cols-[0.72fr_1fr] lg:items-end">
                <div>
                    <p
                        class="text-xs font-black tracking-[0.18em] text-[var(--site-theme-accent)] uppercase"
                    >
                        {{ __('capell-theme-knowledge::generic.search_label') }}
                    </p>
                    <h2
                        class="mt-4 text-4xl font-black tracking-tight text-white"
                    >
                        {{ $heading }}
                    </h2>
                </div>

                @isset($summary)
                    <p class="max-w-2xl text-lg leading-8 text-slate-300">
                        {{ $summary }}
                    </p>
                @endisset
            </div>
        @endisset

        <div
            class="mt-8 border border-white/10 bg-[var(--site-theme-ink-panel)] p-4 shadow-xl shadow-black/20"
        >
            <div class="grid gap-4 lg:grid-cols-[1fr_0.72fr]">
                <div class="border border-white/10 bg-[var(--site-theme-ink-panel-raised)] p-4">
                    <p class="text-sm font-bold text-slate-300">
                        {{ $searchAvailable ?? false ? __('capell-theme-knowledge::generic.search_connected') : __('capell-theme-knowledge::generic.search_static') }}
                    </p>

                    <form
                        method="GET"
                        action="{{ $searchAction }}"
                        role="search"
                        aria-label="{{ __('capell-theme-knowledge::generic.search_form_label') }}"
                        class="mt-5 grid gap-3"
                    >
                        <label
                            for="knowledge-search-listing-query"
                            class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary-soft)] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.search_query_label') }}
                        </label>

                        <div class="grid gap-3 md:grid-cols-[1fr_auto]">
                            <input
                                id="knowledge-search-listing-query"
                                type="search"
                                name="q"
                                value="{{ $searchQuery }}"
                                placeholder="{{ __('capell-theme-knowledge::generic.search_placeholder') }}"
                                autocomplete="off"
                                class="min-h-12 border border-white/10 bg-white px-4 py-3 text-base font-bold text-[var(--site-theme-foreground)] placeholder:text-slate-500 focus:border-[var(--site-theme-accent)] focus:outline-2 focus:outline-offset-2 focus:outline-[var(--site-theme-accent)]"
                            />
                            <button
                                type="submit"
                                class="min-h-12 bg-[var(--site-theme-accent)] px-5 py-3 text-sm font-black text-[var(--site-theme-ink)] focus:outline-2 focus:outline-offset-2 focus:outline-[var(--site-theme-accent)]"
                            >
                                {{ __('capell-theme-knowledge::generic.search_action') }}
                            </button>
                        </div>

                        <p class="text-sm leading-6 text-slate-400">
                            {{ __('capell-theme-knowledge::generic.search_help') }}
                        </p>
                    </form>

                    <div class="mt-5">
                        <p
                            class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary)] uppercase"
                        >
                            {{ __('capell-theme-knowledge::generic.search_filters_label') }}
                        </p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($filters as $filter)
                                <button
                                    type="button"
                                    class="border border-white/10 bg-white/5 px-3 py-2 text-xs font-black text-[var(--site-theme-primary-soft)]"
                                >
                                    {{ $filter }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="border border-[var(--site-theme-accent)]/40 bg-[var(--site-theme-accent)]/10 p-4">
                    <p
                        class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-accent-strong)] uppercase"
                    >
                        {{ __('capell-theme-knowledge::generic.search_source_label') }}
                    </p>
                    <p class="mt-3 text-sm leading-6 text-slate-300">
                        {{ __('capell-theme-knowledge::generic.search_source_summary') }}
                    </p>
                    <div
                        class="mt-4 space-y-3"
                        aria-hidden="true"
                    >
                        <div class="grid grid-cols-[0.32fr_1fr_auto] gap-3">
                            <span class="h-3 bg-[var(--site-theme-accent)]"></span>
                            <span class="h-3 bg-[var(--site-theme-primary)]"></span>
                            <span class="h-3 w-8 bg-[var(--site-theme-primary-soft)]"></span>
                        </div>
                        <div class="grid grid-cols-[0.45fr_1fr_auto] gap-3">
                            <span class="h-3 bg-[var(--site-theme-accent)]"></span>
                            <span class="h-3 bg-[var(--site-theme-primary-muted)]"></span>
                            <span class="h-3 w-8 bg-[var(--site-theme-primary-soft)]"></span>
                        </div>
                        <div class="grid grid-cols-[0.24fr_1fr_auto] gap-3">
                            <span class="h-3 bg-[var(--site-theme-accent)]"></span>
                            <span class="h-3 bg-[var(--site-theme-primary-soft)]"></span>
                            <span class="h-3 w-8 bg-[var(--site-theme-primary-border)]"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-4 grid gap-4 lg:grid-cols-3">
                @foreach ($searchItems as $item)
                    <article
                        class="knowledge-result-card border border-white/10 bg-[var(--site-theme-primary-canvas)] p-4"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <p
                                class="text-xs font-black tracking-[0.16em] text-[var(--site-theme-primary)] uppercase"
                            >
                                {{ $item['type'] ?? __('capell-theme-knowledge::generic.article_signal') }}
                            </p>
                            <p
                                class="bg-[var(--site-theme-accent-panel)] px-2 py-1 text-xs font-black text-[var(--site-theme-accent-contrast)]"
                            >
                                {{ __('capell-theme-knowledge::generic.search_score_label') }}
                                {{ $item['score'] ?? '92%' }}
                            </p>
                        </div>
                        <h3 class="mt-3 text-lg font-black text-[var(--site-theme-foreground)]">
                            {{ $item['title'] }}
                        </h3>
                        <p class="mt-2 text-sm leading-6 text-slate-600">
                            {{ $item['summary'] ?? $item['description'] ?? '' }}
                        </p>
                        <div
                            class="mt-4 flex flex-wrap gap-2 text-xs font-bold"
                        >
                            @foreach (($item['meta'] ?? []) ?: [__('capell-theme-knowledge::generic.library_signal')] as $meta)
                                <span
                                    class="bg-[var(--site-theme-primary-border)] px-3 py-1 text-[var(--site-theme-primary-strong)]"
                                >
                                    {{ $meta }}
                                </span>
                            @endforeach
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </div>
</section>
