<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\BuildPageSeoReportAction;
use Capell\SeoSuite\Data\PageSeoReportData;
use Capell\SeoSuite\Filament\Widgets\Concerns\ResolvesEditPageRecord;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditPageSeoAuditBadge extends Component
{
    use ResolvesEditPageRecord;

    #[Computed]
    public function issueCount(): ?int
    {
        $report = $this->buildReport();

        return $report instanceof PageSeoReportData ? count($report->issues) : null;
    }

    public function render(): mixed
    {
        return view('capell-seo-suite::livewire.filament.widgets.edit-page-seo-audit-badge');
    }

    private function buildReport(): ?PageSeoReportData
    {
        $record = $this->pageRecord();

        if (! $record instanceof Page) {
            return null;
        }

        $record->loadMissing([
            'site.language',
            'translation.language',
        ]);

        $site = $record->site;
        $language = $record->translation->language ?? $site?->language;

        if (! $site instanceof Site || ! $language instanceof Language) {
            return null;
        }

        return BuildPageSeoReportAction::run($record, $site, $language);
    }
}
