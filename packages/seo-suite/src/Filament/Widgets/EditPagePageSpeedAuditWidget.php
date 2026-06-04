<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Filament\Concerns\HasBlankPlaceholder;
use Capell\Core\Models\Page;
use Capell\SeoSuite\Contracts\PageSpeedInsightsClientInterface;
use Capell\SeoSuite\Enums\PageSpeedStrategyEnum;
use Capell\SeoSuite\Filament\Widgets\Concerns\ResolvesEditPageRecord;
use Capell\SeoSuite\Jobs\RunPageSpeedAuditJob;
use Capell\SeoSuite\Models\PageSpeedAuditResult;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

#[On('refresh-pagespeed-audit')]
class EditPagePageSpeedAuditWidget extends Widget
{
    use HasBlankPlaceholder;
    use ResolvesEditPageRecord;

    public bool $embedded = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'capell-seo-suite::filament.widgets.pagespeed-audit-edit';

    public function runAudit(): void
    {
        $record = $this->pageRecord();

        if (! $record instanceof Page) {
            Notification::make('pagespeed-audit-missing-page')
                ->title(__('capell-seo-suite::generic.pagespeed_missing_page'))
                ->warning()
                ->send();

            return;
        }

        /** @var PageSpeedInsightsClientInterface $client */
        $client = resolve(PageSpeedInsightsClientInterface::class);

        if (! $client->isConfigured()) {
            Notification::make('pagespeed-audit-not-configured')
                ->title(__('capell-seo-suite::generic.pagespeed_not_configured'))
                ->warning()
                ->send();

            return;
        }

        dispatch(new RunPageSpeedAuditJob(
            pageId: (int) $record->getKey(),
            strategies: PageSpeedStrategyEnum::cases(),
        ));

        unset($this->latestResults);

        Notification::make('pagespeed-audit-queued')
            ->title(__('capell-seo-suite::generic.pagespeed_manual_queued'))
            ->success()
            ->send();
    }

    /**
     * @return Collection<'desktop'|'mobile', PageSpeedAuditResult>
     */
    #[Computed]
    public function latestResults(): Collection
    {
        $record = $this->pageRecord();

        if (! $record instanceof Page) {
            return collect();
        }

        return PageSpeedAuditResult::query()
            ->where('page_id', $record->getKey())
            ->latest('fetched_at')
            ->get()
            ->unique(fn (PageSpeedAuditResult $result): string => $result->strategyEnum()->value)
            ->mapWithKeys(fn (PageSpeedAuditResult $result): array => [$result->strategyEnum()->value => $result]);
    }

    public function bandFor(?PageSpeedAuditResult $result): string
    {
        return $result instanceof PageSpeedAuditResult ? $result->performanceBand() : 'no_data';
    }

    /**
     * @return list<PageSpeedStrategyEnum>
     */
    public function strategies(): array
    {
        return PageSpeedStrategyEnum::cases();
    }

    public function bandColor(string $band): string
    {
        return match ($band) {
            'good' => 'success',
            'needs_improvement' => 'warning',
            'poor', 'failed' => 'danger',
            default => 'gray',
        };
    }
}
