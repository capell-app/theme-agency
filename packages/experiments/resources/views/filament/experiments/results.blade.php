@php
    /** @var \Capell\Experiments\Models\Experiment $experiment */
    /** @var \Capell\Experiments\Data\WinnerReportData $report */
@endphp

<div class="space-y-6">
    <dl class="grid gap-4 sm:grid-cols-4">
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('capell-experiments::generic.results.total_allocations') }}</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($report->totalAllocations) }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('capell-experiments::generic.results.total_conversions') }}</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($report->totalConversions) }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('capell-experiments::generic.results.minimum_sample_size') }}</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($report->minimumSampleSize) }}</dd>
        </div>
        <div>
            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('capell-experiments::generic.results.confidence') }}</dt>
            <dd class="mt-1 text-2xl font-semibold text-gray-950 dark:text-white">{{ number_format($report->confidenceLevel * 100, 1) }}%</dd>
        </div>
    </dl>

    @if (! $report->isStatisticallySignificant)
        <p class="rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-700 ring-1 ring-gray-950/10 dark:bg-gray-900 dark:text-gray-200 dark:ring-white/10">
            {{ __('capell-experiments::generic.results.no_winner') }}
        </p>
    @endif

    <div class="overflow-hidden rounded-lg ring-1 ring-gray-950/10 dark:ring-white/10">
        <table class="min-w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
            <thead class="bg-gray-50 dark:bg-gray-900">
                <tr>
                    <th scope="col" class="px-4 py-3 text-left font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.variant') }}</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.allocations') }}</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.conversions') }}</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.conversion_rate') }}</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.lift') }}</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.p_value') }}</th>
                    <th scope="col" class="px-4 py-3 text-right font-semibold text-gray-950 dark:text-white">{{ __('capell-experiments::generic.results.summary') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 bg-white dark:divide-gray-800 dark:bg-gray-950">
                @foreach ($report->variants as $variantReport)
                    <tr>
                        <td class="px-4 py-3 text-gray-950 dark:text-white">
                            <div class="font-medium">{{ $variantReport->variantName }}</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $variantReport->variantKey }}</div>
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($variantReport->allocations) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($variantReport->conversions) }}</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-200">{{ number_format($variantReport->conversionRate * 100, 2) }}%</td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-200">
                            @if ($variantReport->lift === null)
                                {{ __('capell-experiments::generic.results.no_lift') }}
                            @else
                                {{ number_format($variantReport->lift * 100, 2) }}%
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right tabular-nums text-gray-700 dark:text-gray-200">
                            {{ $variantReport->pValue === null ? '-' : number_format($variantReport->pValue, 4) }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-700 dark:text-gray-200">
                            @if ($variantReport->isWinner)
                                {{ __('capell-experiments::generic.results.winner') }}
                            @elseif ($variantReport->isStatisticallySignificant)
                                {{ __('capell-experiments::generic.results.significant') }}
                            @elseif ($variantReport->meetsSampleSize)
                                {{ __('capell-experiments::generic.results.sample_ready') }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
