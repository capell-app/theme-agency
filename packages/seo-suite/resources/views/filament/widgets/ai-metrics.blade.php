<x-filament-widgets::widget>
    <x-filament::section
        :heading="__('capell-seo-suite::dashboard.ai_metrics')"
    >
        <div class="@container space-y-6">
            {{-- Summary Cards --}}
            <div class="@md:grid-cols-5 grid grid-cols-2 gap-4">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::dashboard.ai_total_generations') }}
                    </div>
                    <div
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ $data->totalGenerations }}
                    </div>
                </div>
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                    <div
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::dashboard.ai_total_tokens') }}
                    </div>
                    <div
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ number_format($data->totalTokens) }}
                    </div>
                </div>
                <div class="rounded-lg bg-amber-50 p-4 dark:bg-amber-900/20">
                    <div
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::dashboard.ai_estimated_spend') }}
                    </div>
                    <div
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ $data->currency }} {{ number_format($data->totalCostMicros / 1000000, 2) }}
                    </div>
                </div>
                <div class="rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                    <div
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::dashboard.ai_failed') }}
                    </div>
                    <div
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ $data->failedGenerations }}
                    </div>
                </div>
                <div class="rounded-lg bg-purple-50 p-4 dark:bg-purple-900/20">
                    <div
                        class="text-sm font-medium text-gray-600 dark:text-gray-400"
                    >
                        {{ __('capell-seo-suite::dashboard.ai_remaining_quota') }}
                    </div>
                    <div
                        class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                    >
                        {{ $data->remainingRequests }}
                    </div>
                </div>
            </div>

            {{-- Configuration --}}
            <div>
                <h3
                    class="mb-3 text-sm font-semibold text-gray-900 dark:text-white"
                >
                    {{ __('capell-seo-suite::dashboard.ai_configuration') }}
                </h3>
                <div class="space-y-2">
                    <div
                        class="flex items-center justify-between rounded px-3 py-2 text-sm odd:bg-gray-50 dark:odd:bg-gray-800/50"
                    >
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ __('capell-seo-suite::dashboard.ai_provider') }}
                        </span>
                        <span
                            class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                        >
                            {{ $data->aiProvider }}
                        </span>
                    </div>
                    <div
                        class="flex items-center justify-between rounded px-3 py-2 text-sm even:bg-gray-50 dark:even:bg-gray-800/50"
                    >
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ __('capell-seo-suite::dashboard.ai_model') }}
                        </span>
                        <span
                            class="rounded bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300"
                        >
                            {{ $data->aiModel }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Feature Toggles --}}
            <div>
                <h3
                    class="mb-3 text-sm font-semibold text-gray-900 dark:text-white"
                >
                    {{ __('capell-seo-suite::dashboard.ai_feature_toggles') }}
                </h3>
                <div class="space-y-2">
                    <div
                        class="flex items-center justify-between rounded px-3 py-2 text-sm odd:bg-gray-50 dark:odd:bg-gray-800/50"
                    >
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ __('capell-seo-suite::dashboard.ai_page_content_generator') }}
                        </span>
                        @if ($data->pageContentGeneratorEnabled)
                            <span
                                class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300"
                            >
                                {{ __('capell-seo-suite::dashboard.ai_enabled') }}
                            </span>
                        @else
                            <span
                                class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            >
                                {{ __('capell-seo-suite::dashboard.ai_disabled') }}
                            </span>
                        @endif
                    </div>
                    <div
                        class="flex items-center justify-between rounded px-3 py-2 text-sm even:bg-gray-50 dark:even:bg-gray-800/50"
                    >
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ __('capell-seo-suite::dashboard.ai_page_title_suggestions') }}
                        </span>
                        @if ($data->pageTitleSuggestionsEnabled)
                            <span
                                class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300"
                            >
                                {{ __('capell-seo-suite::dashboard.ai_enabled') }}
                            </span>
                        @else
                            <span
                                class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            >
                                {{ __('capell-seo-suite::dashboard.ai_disabled') }}
                            </span>
                        @endif
                    </div>
                    <div
                        class="flex items-center justify-between rounded px-3 py-2 text-sm odd:bg-gray-50 dark:odd:bg-gray-800/50"
                    >
                        <span class="text-gray-700 dark:text-gray-300">
                            {{ __('capell-seo-suite::dashboard.ai_creator') }}
                        </span>
                        @if ($data->aiCreatorEnabled)
                            <span
                                class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300"
                            >
                                {{ __('capell-seo-suite::dashboard.ai_enabled') }}
                            </span>
                        @else
                            <span
                                class="rounded bg-gray-100 px-2 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-300"
                            >
                                {{ __('capell-seo-suite::dashboard.ai_disabled') }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Feature Usage --}}
            @if ($data->featureUsage->count() > 0)
                <details class="group">
                    <summary
                        class="flex cursor-pointer items-center justify-between gap-2 py-2 text-sm font-medium text-gray-900 dark:text-white"
                    >
                        <span>
                            {{ __('capell-seo-suite::dashboard.ai_feature_usage_breakdown') }}
                        </span>
                        <svg
                            class="h-4 w-4 transition-transform group-open:rotate-180"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </summary>
                    <div class="mt-2 space-y-2 pl-2">
                        @foreach ($data->featureUsage as $feature)
                            <div
                                class="space-y-1 rounded px-2 py-1 text-xs odd:bg-gray-50 dark:odd:bg-gray-800/50"
                            >
                                <div class="flex items-center justify-between">
                                    <span
                                        class="font-medium text-gray-700 dark:text-gray-300"
                                    >
                                        {{ $feature->feature }}
                                    </span>
                                    <span
                                        class="text-gray-600 dark:text-gray-400"
                                    >
                                        {{ $feature->count }} requests
                                    </span>
                                </div>
                                <div
                                    class="flex items-center justify-between text-xs text-gray-600 dark:text-gray-400"
                                >
                                    <span>
                                        {{ number_format($feature->tokens) }} tokens
                                        (Ø {{ number_format($feature->averageTokensPerRequest, 1) }}/req)
                                    </span>
                                    <span>
                                        {{ $data->currency }} {{ number_format($feature->costMicros / 1000000, 2) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </details>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
