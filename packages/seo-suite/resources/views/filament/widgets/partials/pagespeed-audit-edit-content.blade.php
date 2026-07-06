@if ($this->latestResults->isEmpty())
    <p class="text-sm text-gray-500 dark:text-gray-400">
        {{ __('capell-seo-suite::generic.pagespeed_no_results') }}
    </p>
@else
    <div class="grid grid-cols-1 gap-3 lg:grid-cols-2">
        @foreach ($this->strategies() as $strategy)
            @php
                $result = $this->latestResults->get($strategy->value);
                $band = $this->bandFor($result);
                $metrics = is_array($result?->metrics) ? $result->metrics : [];
                $opportunities = is_array($result?->opportunities) ? $result->opportunities : [];
                $diagnostics = is_array($result?->diagnostics) ? $result->diagnostics : [];
            @endphp

            <div
                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
            >
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <h3
                            class="text-sm font-semibold text-gray-950 dark:text-white"
                        >
                            {{ $strategy->getLabel() }}
                        </h3>
                        <p
                            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ $result?->fetched_at?->diffForHumans() ?? __('capell-seo-suite::generic.pagespeed_never_run') }}
                        </p>
                    </div>
                    <x-filament::badge :color="$this->bandColor($band)">
                        {{ $result?->performance_score ?? '-' }}
                    </x-filament::badge>
                </div>

                @if ($result?->status === 'failed')
                    <p
                        class="text-danger-600 dark:text-danger-400 mt-3 text-sm"
                    >
                        {{ $result->error_message ?? __('capell-seo-suite::generic.pagespeed_failed') }}
                    </p>
                @elseif ($result !== null)
                    <dl class="mt-4 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.pagespeed_accessibility') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ $result->accessibility_score ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.pagespeed_best_practices') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ $result->best_practices_score ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.pagespeed_seo') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ $result->seo_score ?? '-' }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-gray-500 dark:text-gray-400">
                                {{ __('capell-seo-suite::generic.pagespeed_lighthouse') }}
                            </dt>
                            <dd
                                class="font-medium text-gray-950 dark:text-white"
                            >
                                {{ $result->lighthouse_version ?? '-' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($metrics !== [])
                        <div class="mt-4">
                            <h4
                                class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-seo-suite::generic.pagespeed_metrics') }}
                            </h4>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($metrics as $metricKey => $metric)
                                    <span
                                        class="rounded-md bg-gray-100 px-2 py-1 text-xs text-gray-700 dark:bg-gray-800 dark:text-gray-200"
                                    >
                                        {{ __('capell-seo-suite::generic.pagespeed_metric_' . str_replace('-', '_', $metricKey)) }}: {{ $metric['display_value'] ?? '-' }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="mt-4 grid grid-cols-1 gap-3 xl:grid-cols-2">
                        <div>
                            <h4
                                class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-seo-suite::generic.pagespeed_opportunities') }}
                            </h4>
                            @forelse (array_slice($opportunities, 0, 3) as $item)
                                <p
                                    class="mt-2 text-sm text-gray-700 dark:text-gray-200"
                                >
                                    {{ $item['title'] ?? '' }}
                                    @if (($item['display_value'] ?? null) !== null)
                                        <span
                                            class="text-gray-500 dark:text-gray-400"
                                        >
                                            ({{ $item['display_value'] }})
                                        </span>
                                    @endif
                                </p>
                            @empty
                                <p
                                    class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ __('capell-seo-suite::generic.pagespeed_no_opportunities') }}
                                </p>
                            @endforelse
                        </div>
                        <div>
                            <h4
                                class="text-xs font-semibold text-gray-500 uppercase dark:text-gray-400"
                            >
                                {{ __('capell-seo-suite::generic.pagespeed_diagnostics') }}
                            </h4>
                            @forelse (array_slice($diagnostics, 0, 3) as $item)
                                <p
                                    class="mt-2 text-sm text-gray-700 dark:text-gray-200"
                                >
                                    {{ $item['title'] ?? '' }}
                                </p>
                            @empty
                                <p
                                    class="mt-2 text-sm text-gray-500 dark:text-gray-400"
                                >
                                    {{ __('capell-seo-suite::generic.pagespeed_no_diagnostics') }}
                                </p>
                            @endforelse
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('capell-seo-suite::generic.pagespeed_strategy_no_result') }}
                    </p>
                @endif
            </div>
        @endforeach
    </div>
@endif
