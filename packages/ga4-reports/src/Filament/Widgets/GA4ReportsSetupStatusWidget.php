<?php

declare(strict_types=1);

namespace Capell\GA4Reports\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\GA4Reports\Actions\ResolveGA4ReportsConfigAction;
use Capell\GA4Reports\Models\GA4ReportsSyncRun;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Collection;
use Override;

final class GA4ReportsSetupStatusWidget extends BaseWidget implements CapellWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'ga4_reports_sync_status';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['md' => 1];

    protected static ?int $sort = 24;

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->getRecords())
            ->queryStringIdentifier('ga4-reports-sync-status')
            ->paginated(false)
            ->searchable(false)
            ->heading(__('capell-ga4-reports::widgets.sync_status'))
            ->columns([
                TextColumn::make('label')
                    ->label(__('capell-ga4-reports::widgets.metric')),
                TextColumn::make('value')
                    ->label(__('capell-ga4-reports::widgets.value')),
            ]);
    }

    /**
     * @return Collection<array-key, mixed>
     */
    private function getRecords(): Collection
    {
        $config = ResolveGA4ReportsConfigAction::run();
        $latestRun = GA4ReportsSyncRun::query()->latest('started_at')->first();

        return collect([
            [
                'id' => 'configured',
                'label' => (string) __('capell-ga4-reports::widgets.configured'),
                'value' => $config->enabled && $config->propertyId !== '' && $config->credentialsPath !== ''
                    ? (string) __('capell-ga4-reports::widgets.yes')
                    : (string) __('capell-ga4-reports::widgets.no'),
            ],
            [
                'id' => 'property-id',
                'label' => (string) __('capell-ga4-reports::settings.property_id'),
                'value' => $config->propertyId !== '' ? (string) $config->propertyId : (string) __('capell-ga4-reports::widgets.not_set'),
            ],
            [
                'id' => 'last-sync',
                'label' => (string) __('capell-ga4-reports::widgets.last_sync'),
                'value' => $latestRun instanceof GA4ReportsSyncRun && $latestRun->finished_at !== null
                    ? $latestRun->finished_at->toDateTimeString()
                    : (string) __('capell-ga4-reports::widgets.never'),
            ],
            [
                'id' => 'last-status',
                'label' => (string) __('capell-ga4-reports::widgets.last_status'),
                'value' => $latestRun instanceof GA4ReportsSyncRun ? $latestRun->status : (string) __('capell-ga4-reports::widgets.not_available'),
            ],
        ]);
    }
}
