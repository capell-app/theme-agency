<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions\Dashboard;

use Capell\SeoSuite\Actions\BuildSeoIntelligenceOpportunitiesAction;
use Capell\SeoSuite\Data\SeoOpportunityRowData;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static Collection<int, array{id: string, type: string, query: string, url: string, message: string, priority: int, impressions: int, clicks: int, ctr: float, average_position: string}> run(int $limit = 5)
 */
final class BuildSeoIntelligenceRowsAction
{
    use AsAction;

    /**
     * @return Collection<int, array{id: string, type: string, query: string, url: string, message: string, priority: int, impressions: int, clicks: int, ctr: float, average_position: string}>
     */
    public function handle(int $limit = 5): Collection
    {
        return BuildSeoIntelligenceOpportunitiesAction::run($limit)
            ->map(fn (SeoOpportunityRowData $row, int $index): array => [
                'id' => 'seo-intelligence-' . $index . '-' . hash('sha256', $row->type->value . $row->query . $row->url),
                'type' => $row->type->getLabel(),
                'query' => $row->query,
                'url' => $row->url,
                'message' => $row->message,
                'priority' => $row->priority,
                'impressions' => $row->impressions,
                'clicks' => $row->clicks,
                'ctr' => round($row->ctr * 100, 1),
                'average_position' => $row->averagePosition === null ? (string) __('capell-seo-suite::dashboard.not_available') : number_format($row->averagePosition, 1),
            ]);
    }
}
