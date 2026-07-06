<x-filament-widgets::widget class="capell-content-graph-health">
    <x-filament::section
        heading="{{ __('capell-diagnostics::package.content_graph_health_heading') }}"
    >
        <div class="grid gap-3 sm:grid-cols-3">
            <div
                class="rounded border border-gray-200 p-3 dark:border-gray-800"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('capell-diagnostics::package.content_graph_health_edges') }}
                </div>
                <div
                    class="text-2xl font-semibold text-gray-950 dark:text-white"
                >
                    {{ $this->data->totalEdges }}
                </div>
            </div>
            <div
                class="rounded border border-gray-200 p-3 dark:border-gray-800"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('capell-diagnostics::package.content_graph_health_stale_sources') }}
                </div>
                <div
                    class="text-2xl font-semibold text-gray-950 dark:text-white"
                >
                    {{ $this->data->staleSourceEdges }}
                </div>
            </div>
            <div
                class="rounded border border-gray-200 p-3 dark:border-gray-800"
            >
                <div class="text-xs text-gray-500 dark:text-gray-400">
                    {{ __('capell-diagnostics::package.content_graph_health_stale_targets') }}
                </div>
                <div
                    class="text-2xl font-semibold text-gray-950 dark:text-white"
                >
                    {{ $this->data->staleTargetEdges }}
                </div>
            </div>
        </div>

        <div class="mt-4 space-y-1">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">
                {{ __('capell-diagnostics::package.content_graph_health_high_impact_targets') }}
            </div>

            @forelse ($this->data->highImpactTargets as $target)
                <div
                    class="flex items-center justify-between gap-2 rounded px-2 py-1 text-xs odd:bg-gray-50 dark:odd:bg-gray-800/50"
                >
                    <span
                        class="truncate font-mono text-gray-700 dark:text-gray-200"
                    >
                        {{ class_basename($target['target_type']) }} #{{ $target['target_id'] }}
                    </span>
                    <span
                        class="rounded-full bg-gray-100 px-2 py-0.5 text-gray-600 dark:bg-gray-800 dark:text-gray-300"
                    >
                        {{ $target['count'] }}
                    </span>
                </div>
            @empty
                <p class="text-xs text-gray-400">
                    {{ __('capell-diagnostics::package.content_graph_health_none_recorded') }}
                </p>
            @endforelse
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
