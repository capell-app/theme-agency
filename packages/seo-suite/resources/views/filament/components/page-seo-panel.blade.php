<div class="space-y-4">
    @if (! $hasReport)
        <div class="text-sm text-gray-600 dark:text-gray-300">
            {{ __('capell-seo-suite::generic.seo_panel_empty_state') }}
        </div>
    @else
        <section class="space-y-3">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <div
                        class="text-sm font-medium text-gray-950 dark:text-white"
                    >
                        {{ __('capell-seo-suite::generic.seo_panel_overview') }}
                    </div>
                    <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                        {{ __('capell-seo-suite::generic.seo_panel_passed_checks', ['count' => count($passedCheckValues)]) }}
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    {{ $schemaComponent->getAction('ai_content_brief') }}

                    <div
                        class="rounded-md bg-gray-50 px-3 py-2 text-right dark:bg-gray-800"
                    >
                        <div
                            class="text-xs font-medium text-gray-500 dark:text-gray-400"
                        >
                            {{ __('capell-seo-suite::generic.seo_panel_score') }}
                        </div>
                        <div
                            class="text-2xl font-semibold text-gray-950 dark:text-white"
                        >
                            {{ $report->score }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                <div
                    class="rounded-md border border-gray-200 p-3 dark:border-gray-700"
                >
                    <div
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::generic.seo_panel_search_preview') }}
                    </div>
                    <div
                        class="text-primary-600 dark:text-primary-400 mt-2 text-base font-medium"
                    >
                        {{ $report->searchPreview->title }}
                    </div>
                    <div
                        class="mt-1 text-xs text-green-700 dark:text-green-400"
                    >
                        {{ $report->searchPreview->url }}
                    </div>
                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ $report->searchPreview->description }}
                    </div>
                </div>

                <div
                    class="rounded-md border border-gray-200 p-3 dark:border-gray-700"
                >
                    <div
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::generic.seo_panel_social_preview') }}
                    </div>
                    <div
                        class="mt-2 text-base font-medium text-gray-950 dark:text-white"
                    >
                        {{ $report->socialPreview->title }}
                    </div>
                    <div class="mt-1 text-sm text-gray-700 dark:text-gray-300">
                        {{ $report->socialPreview->description }}
                    </div>
                    @if ($report->socialPreview->imageUrl !== null)
                        <div
                            class="mt-2 text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ $report->socialPreview->imageUrl }}
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid gap-3 md:grid-cols-3">
                @foreach ([
                              __('capell-seo-suite::generic.seo_severity_critical') => $overviewIssues['critical'],
                              __('capell-seo-suite::generic.seo_severity_warning') => $overviewIssues['warning'],
                              __('capell-seo-suite::generic.seo_severity_notice') => $overviewIssues['notice'],
                          ] as $severityLabel => $issues)
                    <div
                        class="rounded-md border border-gray-200 p-3 dark:border-gray-700"
                    >
                        <div
                            class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                        >
                            {{ $severityLabel }} ({{ count($issues) }})
                        </div>

                        @if ($issues === [])
                            <div
                                class="mt-2 text-sm text-gray-600 dark:text-gray-300"
                            >
                                {{ __('capell-seo-suite::generic.seo_panel_section_clear') }}
                            </div>
                        @else
                            <ul
                                class="mt-2 space-y-2 text-sm text-gray-700 dark:text-gray-300"
                            >
                                @foreach ($issues as $issue)
                                    <li>{{ $issue->message }}</li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                @endforeach
            </div>

            @if ($scoreBreakdown !== null)
                <div class="space-y-2">
                    <div
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::generic.seo_panel_score_breakdown') }}
                    </div>
                    <div class="grid gap-2 md:grid-cols-4">
                        @foreach ($scoreBreakdown->categories as $category)
                            <div
                                class="rounded-md border border-gray-200 p-3 dark:border-gray-700"
                            >
                                <div
                                    class="flex items-center justify-between gap-2 text-sm"
                                >
                                    <span
                                        class="font-medium text-gray-700 dark:text-gray-200"
                                    >
                                        {{ $category->label }}
                                    </span>
                                    <span
                                        class="font-semibold text-gray-950 dark:text-white"
                                    >
                                        {{ $category->score }}
                                    </span>
                                </div>
                                <div
                                    class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                                >
                                    {{ __('capell-seo-suite::generic.seo_panel_category_weight', ['weight' => $category->weight]) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if ($contentAnalysis !== null)
                <div
                    class="rounded-md border border-gray-200 p-3 dark:border-gray-700"
                >
                    <div
                        class="text-xs font-medium tracking-wide text-gray-500 uppercase dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::generic.seo_panel_content_analysis') }}
                    </div>
                    <dl class="mt-2 grid gap-2 text-sm md:grid-cols-4">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.seo_panel_word_count') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ number_format($contentAnalysis->wordCount) }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.seo_panel_h1_count') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ $contentAnalysis->h1Count }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.seo_panel_focus_keyword') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ $contentAnalysis->focusKeyword ?? __('capell-seo-suite::generic.seo_panel_missing_metric') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.seo_panel_keyword_density') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ number_format($contentAnalysis->focusKeywordDensity, 2) }}%
                            </dd>
                        </div>
                    </dl>
                </div>
            @endif
        </section>
    @endif
</div>
