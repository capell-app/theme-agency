<x-filament-widgets::widget>
    <x-filament::section :heading="__('capell-seo-suite::dashboard.ai_usage')">
        <div>
            <div class="text-2xl font-bold">
                {{ number_format($generationCount) }}
            </div>
            <div class="text-sm text-gray-500">
                {{ __('capell-seo-suite::dashboard.ai_generations') }}
            </div>
        </div>
        <div>
            <div class="text-2xl font-bold">
                {{ number_format($totalTokens) }}
            </div>
            <div class="text-sm text-gray-500">
                {{ __('capell-seo-suite::dashboard.ai_total_tokens') }}
            </div>
        </div>
        <div>
            <div class="text-2xl font-bold">
                {{ $totalCost }}
            </div>
            <div class="text-sm text-gray-500">
                {{ __('capell-seo-suite::dashboard.ai_estimated_spend') }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
