<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Actions\Dashboard;

use Capell\Admin\Support\SiteScope;
use Capell\SeoSuite\Models\PageSeoSnapshot;
use Illuminate\Support\Collection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static \Illuminate\Support\Collection<int, array{id: string, page: string, score: int, critical_count: int, warning_count: int, notices: int}> run(int $limit = 5)
 */
final class BuildSeoOpportunityRowsAction
{
    use AsAction;

    /**
     * @return Collection<array-key, mixed>
     */
    public function handle(int $limit = 5): Collection
    {
        return SiteScope::applyForCurrentActor(PageSeoSnapshot::query(), denyWhenMissingActor: true)
            ->with('page')
            ->orderByDesc('critical_count')
            ->orderByDesc('warning_count')
            ->orderBy('score')
            ->limit($limit)
            ->get()
            ->map(fn (PageSeoSnapshot $snapshot): array => [
                'id' => 'seo-opportunity-' . $snapshot->id,
                'page' => $this->pageLabel($snapshot),
                'score' => $snapshot->score,
                'critical_count' => $snapshot->critical_count,
                'warning_count' => $snapshot->warning_count,
                'notices' => $snapshot->notice_count,
            ])
            ->values();
    }

    private function pageLabel(PageSeoSnapshot $snapshot): string
    {
        $page = $snapshot->page;

        if ($page === null) {
            return __('capell-seo-suite::dashboard.deleted_page');
        }

        foreach (['name', 'title', 'slug'] as $attribute) {
            $value = $page->getAttribute($attribute);

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return 'Page #' . $page->getKey();
    }
}
