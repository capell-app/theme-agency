<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\SeoSuite\Models\AIGenerationHistory;
use Filament\Widgets\Widget;
use Override;

class AiUsageFilamentWidget extends Widget
{
    protected string $view = 'capell-seo-suite::filament.widgets.ai-usage';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    #[Override]
    protected function getViewData(): array
    {
        $count = AIGenerationHistory::query()->count();
        $tokens = AIGenerationHistory::query()->sum('total_tokens');
        $costMicros = AIGenerationHistory::query()->sum('cost_micros');
        $currency = AIGenerationHistory::query()
            ->whereNotNull('cost_currency')
            ->latest('id')
            ->value('cost_currency') ?? config('capell-ai-orchestrator.ai_costs.currency', 'USD');

        return [
            'generationCount' => $count,
            'totalTokens' => $tokens,
            'totalCost' => $this->formatCost($this->integerValue($costMicros), $this->stringValue($currency, 'USD')),
        ];
    }

    private function formatCost(int $costMicros, string $currency): string
    {
        return $currency . ' ' . number_format($costMicros / 1_000_000, 2);
    }

    private function integerValue(mixed $value): int
    {
        return is_numeric($value) ? (int) $value : 0;
    }

    private function stringValue(mixed $value, string $fallback = ''): string
    {
        return is_scalar($value) ? (string) $value : $fallback;
    }
}
