<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('capell-seo-suite::generic.seo_overview')"
        icon="heroicon-o-chart-bar-square"
    >
        @php
            $totals = $this->totals;
        @endphp

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="text-center">
                <div class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ $totals['total'] }}
                </div>
                <div
                    class="mt-1 flex items-center justify-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                >
                    <span>
                        {{ __('capell-seo-suite::generic.stat_total') }}
                    </span>
                    <div
                        x-tooltip.raw="{{ __('capell-seo-suite::generic.stat_total_tooltip') }}"
                        class="cursor-help"
                    >
                        <x-filament::icon
                            icon="heroicon-o-question-mark-circle"
                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                        />
                    </div>
                </div>
            </div>

            <div class="text-center">
                <div
                    @class ([
                        'text-2xl font-bold',
                        'text-warning-600 dark:text-warning-400' => $totals['missingDescription'] > 0,
                        'text-success-600 dark:text-success-400' => $totals['missingDescription'] === 0,
                    ])
                >
                    {{ $totals['missingDescription'] }}
                </div>
                <div
                    class="mt-1 flex items-center justify-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                >
                    <span>
                        {{ __('capell-seo-suite::generic.stat_missing_description') }}
                    </span>
                    <div
                        x-tooltip.raw="{{ __('capell-seo-suite::generic.stat_missing_description_tooltip') }}"
                        class="cursor-help"
                    >
                        <x-filament::icon
                            icon="heroicon-o-question-mark-circle"
                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                        />
                    </div>
                </div>
            </div>

            <div class="text-center">
                <div
                    @class ([
                        'text-2xl font-bold',
                        'text-warning-600 dark:text-warning-400' => $totals['titleIssues'] > 0,
                        'text-success-600 dark:text-success-400' => $totals['titleIssues'] === 0,
                    ])
                >
                    {{ $totals['titleIssues'] }}
                </div>
                <div
                    class="mt-1 flex items-center justify-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                >
                    <span>
                        {{ __('capell-seo-suite::generic.stat_title_issues') }}
                    </span>
                    <div
                        x-tooltip.raw="{{ __('capell-seo-suite::generic.stat_title_issues_tooltip') }}"
                        class="cursor-help"
                    >
                        <x-filament::icon
                            icon="heroicon-o-question-mark-circle"
                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                        />
                    </div>
                </div>
            </div>

            <div class="text-center">
                <div
                    @class ([
                        'text-2xl font-bold',
                        'text-warning-600 dark:text-warning-400' => $totals['duplicateTitles'] > 0,
                        'text-success-600 dark:text-success-400' => $totals['duplicateTitles'] === 0,
                    ])
                >
                    {{ $totals['duplicateTitles'] }}
                </div>
                <div
                    class="mt-1 flex items-center justify-center gap-1 text-xs text-gray-500 dark:text-gray-400"
                >
                    <span>
                        {{ __('capell-seo-suite::generic.stat_duplicate_titles') }}
                    </span>
                    <div
                        x-tooltip.raw="{{ __('capell-seo-suite::generic.stat_duplicate_titles_tooltip') }}"
                        class="cursor-help"
                    >
                        <x-filament::icon
                            icon="heroicon-o-question-mark-circle"
                            class="h-3.5 w-3.5 text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-400"
                        />
                    </div>
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
