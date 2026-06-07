<?php

declare(strict_types=1);

namespace Capell\Experiments\Filament\Resources\Experiments\Pages;

use BackedEnum;
use Capell\Experiments\Actions\BuildWinnerReportAction;
use Capell\Experiments\Data\WinnerReportData;
use Capell\Experiments\Filament\Resources\Experiments\ExperimentResource;
use Capell\Experiments\Models\Experiment;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Override;

/**
 * @property Experiment $record
 */
final class ExperimentResultsPage extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ExperimentResource::class;

    protected static ?string $slug = '{record}/results';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'capell-experiments::filament.experiments.results-page';

    public function mount(string|int $record): void
    {
        $resolvedRecord = $this->resolveRecord($record);

        abort_unless($resolvedRecord instanceof Experiment, 404);

        $this->record = $resolvedRecord;

        $this->authorizeResourceAccess();
    }

    #[Override]
    public function getTitle(): string|Htmlable
    {
        return __('capell-experiments::generic.results.heading', ['name' => $this->record->name]);
    }

    public function getReport(): WinnerReportData
    {
        return BuildWinnerReportAction::run($this->record);
    }

    /**
     * @return array<int, Action>
     */
    #[Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('edit')
                ->label(__('capell-experiments::generic.actions.edit'))
                ->icon('heroicon-o-pencil-square')
                ->url(fn (): string => static::getResource()::getUrl('edit', ['record' => $this->record])),
        ];
    }
}
