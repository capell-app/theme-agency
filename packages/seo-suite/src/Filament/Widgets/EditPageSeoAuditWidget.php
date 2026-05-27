<?php

declare(strict_types=1);

namespace Capell\SeoSuite\Filament\Widgets;

use Capell\Admin\Filament\Concerns\HasBlankPlaceholder;
use Capell\Core\Contracts\Pageable;
use Capell\Core\Models\Language;
use Capell\Core\Models\Page;
use Capell\Core\Models\Site;
use Capell\SeoSuite\Actions\BuildPageSeoReportAction;
use Capell\SeoSuite\Data\PageSeoReportData;
use Capell\SeoSuite\Data\SeoCheckData;
use Capell\SeoSuite\Data\SeoIssueData;
use Capell\SeoSuite\Enums\SeoCheckKeyEnum;
use Filament\Widgets\Widget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Lang;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;

#[On('refresh-seo-audit')]
class EditPageSeoAuditWidget extends Widget
{
    use HasBlankPlaceholder;

    public ?Pageable $record = null;

    public ?int $recordKey = null;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'capell-seo-suite::filament.widgets.seo-audit-edit';

    private ?Pageable $resolvedRecord = null;

    public function mount(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    public function hydrate(): void
    {
        $this->recordKey ??= $this->initialRecordKey();
    }

    /**
     * @return Collection<string, SeoCheckData>
     */
    #[Computed]
    public function checks(): Collection
    {
        $report = $this->buildReport();

        if (! $report instanceof PageSeoReportData) {
            return collect();
        }

        return collect([...$report->passedChecks, ...$report->issues])
            ->filter(fn (mixed $entry): bool => $entry instanceof SeoIssueData)
            ->groupBy(fn (SeoIssueData $issue): string => $issue->key->value)
            ->map(fn (Collection $issues, string $key): SeoCheckData => $this->checkFromIssues($key, $issues));
    }

    /**
     * @param  Collection<int, SeoIssueData>  $issues
     */
    private function checkFromIssues(string $key, Collection $issues): SeoCheckData
    {
        $checkKey = SeoCheckKeyEnum::from($key);
        $hasFailure = $issues->contains(fn (SeoIssueData $issue): bool => $issue->severity->value !== 'passed');

        return new SeoCheckData(
            label: $checkKey->getLabel(),
            pass: ! $hasFailure,
            detail: $hasFailure
                ? $issues->pluck('message')->filter()->unique()->join(' ')
                : $issues->first()?->message,
            icon: $this->iconForCheck($checkKey),
            tooltip: $this->tooltipForCheck($checkKey),
        );
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
        $language = $record->translation->language ?? $site->language;

        if (! $site instanceof Site || ! $language instanceof Language) {
            return null;
        }

        return BuildPageSeoReportAction::run($record, $site, $language);
    }

    private function iconForCheck(SeoCheckKeyEnum $checkKey): string
    {
        return match ($checkKey) {
            SeoCheckKeyEnum::MetaDescription => 'heroicon-o-document-text',
            SeoCheckKeyEnum::MetaTitle => 'heroicon-o-cursor-arrow-rays',
            SeoCheckKeyEnum::DuplicateTitle => 'heroicon-o-document-duplicate',
            SeoCheckKeyEnum::Robots => 'heroicon-o-no-symbol',
            SeoCheckKeyEnum::Canonical => 'heroicon-o-link',
            SeoCheckKeyEnum::InternalLinks => 'heroicon-o-link',
            SeoCheckKeyEnum::ImageAltText, SeoCheckKeyEnum::SocialImage => 'heroicon-o-photo',
            default => 'heroicon-o-magnifying-glass',
        };
    }

    private function pageRecord(): ?Pageable
    {
        if ($this->resolvedRecord instanceof Pageable) {
            return $this->resolvedRecord;
        }

        if ($this->record instanceof Pageable) {
            $this->recordKey ??= (int) $this->record->getKey();
            $this->resolvedRecord = $this->record;

            return $this->resolvedRecord;
        }

        if ($this->recordKey === null) {
            return null;
        }

        $this->resolvedRecord = Page::query()->find($this->recordKey);

        return $this->resolvedRecord;
    }

    private function initialRecordKey(): ?int
    {
        if ($this->record instanceof Pageable) {
            return (int) $this->record->getKey();
        }

        $routeRecord = request()->route('record');

        if ($routeRecord instanceof Pageable) {
            return (int) $routeRecord->getKey();
        }

        if (is_numeric($routeRecord)) {
            return (int) $routeRecord;
        }

        return null;
    }

    private function tooltipForCheck(SeoCheckKeyEnum $checkKey): ?string
    {
        $translationKey = 'capell-seo-suite::generic.seo_check_' . $checkKey->value . '_tooltip';

        return Lang::has($translationKey) ? __($translationKey) : null;
    }
}
