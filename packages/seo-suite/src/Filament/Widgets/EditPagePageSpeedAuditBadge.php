<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Core\Models\Page;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Filament\Widgets\Concerns\ResolvesEditPageRecord;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditPagePageSpeedAuditBadge extends Component
{
    use ResolvesEditPageRecord;

    #[Computed]
    public function issueCount(): ?int
    {
        $record = $this->pageRecord();

        if (! $record instanceof Page) {
            return null;
        }

        $results = $this->latestResults($record);

        return collect(PageSpeedStrategyEnum::cases())
            ->filter(function (PageSpeedStrategyEnum $strategy) use ($results): bool {
                $result = $results->get($strategy->value);

                return ! $result instanceof PageSpeedAuditResult
                    || in_array($result->performanceBand(), ['failed', 'needs_improvement', 'poor'], true);
            })
            ->count();
    }

    public function render(): mixed
    {
        return view('capell-seo-suite::livewire.filament.widgets.edit-page-pagespeed-audit-badge');
    }

    /**
     * @return Collection<'desktop'|'mobile', PageSpeedAuditResult>
     */
    private function latestResults(Page $record): Collection
    {
        return PageSpeedAuditResult::query()
            ->where('page_id', $record->getKey())
            ->latest('fetched_at')
            ->get()
            ->unique(fn (PageSpeedAuditResult $result): string => $result->strategyEnum()->value)
            ->mapWithKeys(fn (PageSpeedAuditResult $result): array => [$result->strategyEnum()->value => $result]);
    }
}
