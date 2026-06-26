<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\AIOrchestrator\Models\AIGenerationHistory;
use Capell\AIOrchestrator\Settings\AIOrchestratorSettings;
use Capell\AIOrchestrator\Support\Ai\AiRateLimiter;
use Capell\SeoSuite\Data\Dashboard\AiMetricsData;
use Capell\SeoSuite\Data\Dashboard\FeatureUsageData;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Override;

final class AiMetricsFilamentWidget extends Widget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;

    protected static string $settingsKey = 'ai_metrics';

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['developer', 'admin', 'super_admin'];

    protected string $view = 'capell-seo-suite::filament.widgets.ai-metrics';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    public function getHeading(): string
    {
        return __('capell-seo-suite::dashboard.ai_metrics');
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    protected function getViewData(): array
    {
        return [
            'data' => $this->getData(),
        ];
    }

    private function getData(): AiMetricsData
    {
        $settings = resolve(AIOrchestratorSettings::class);
        $rateLimiter = resolve(AiRateLimiter::class);

        // Total counts
        $totalGenerations = AIGenerationHistory::query()->count();
        $totalTokens = AIGenerationHistory::query()->sum('total_tokens') ?? 0;
        $totalCostMicros = AIGenerationHistory::query()->sum('cost_micros') ?? 0;
        $currency = $this->stringValue(AIGenerationHistory::query()
            ->whereNotNull('cost_currency')
            ->latest('id')
            ->value('cost_currency'), $this->stringValue(config('capell-ai-orchestrator.ai_costs.currency', 'USD'), 'USD'));
        $failedGenerations = AIGenerationHistory::query()
            ->whereNotNull('error_message')
            ->count();

        // Rate limit status
        $remainingRequests = $rateLimiter->getRemainingRequests('global');
        $windowLimitSeconds = config('ai-orchestrator.rate_limit.window_seconds', 60);
        $lastWindowEnd = null;

        // Feature usage
        $featureUsage = $this->getFeatureUsage();

        return new AiMetricsData(
            totalGenerations: $totalGenerations,
            totalTokens: (int) $totalTokens,
            totalCostMicros: (int) $totalCostMicros,
            currency: $currency,
            failedGenerations: $failedGenerations,
            remainingRequests: $remainingRequests,
            windowLimitSeconds: $windowLimitSeconds,
            lastWindowEnd: $lastWindowEnd,
            aiProvider: $settings->ai_provider ?? 'openai',
            aiModel: $settings->ai_model ?? 'gpt-4-turbo',
            pageContentGeneratorEnabled: $settings->page_content_generator ?? false,
            pageTitleSuggestionsEnabled: $settings->page_title_suggestions ?? false,
            aiCreatorEnabled: $settings->ai_creator ?? false,
            featureUsage: $featureUsage,
        );
    }

    /**
     * @return Collection<int, FeatureUsageData>
     */
    private function getFeatureUsage(): Collection
    {
        $features = AIGenerationHistory::query()
            ->select('action')
            ->distinct()
            ->pluck('action');

        $featureData = [];
        foreach ($features as $feature) {
            $feature = $this->stringValue($feature);

            if ($feature === '') {
                continue;
            }

            $count = AIGenerationHistory::query()
                ->where('action', $feature)
                ->count();
            $tokens = AIGenerationHistory::query()
                ->where('action', $feature)
                ->sum('total_tokens') ?? 0;
            $costMicros = AIGenerationHistory::query()
                ->where('action', $feature)
                ->sum('cost_micros') ?? 0;

            $featureData[] = new FeatureUsageData(
                feature: $feature,
                count: $count,
                tokens: $this->integerValue($tokens),
                costMicros: $this->integerValue($costMicros),
                averageTokensPerRequest: $count > 0 ? $this->integerValue($tokens) / $count : 0,
            );
        }

        // Sort by count descending
        usort($featureData, fn (FeatureUsageData $a, FeatureUsageData $b): int => $b->count <=> $a->count);

        return collect($featureData);
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
