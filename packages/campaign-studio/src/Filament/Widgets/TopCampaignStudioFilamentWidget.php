<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Admin\Filament\Concerns\HasDashboardDateRange;
use Capell\CampaignStudio\Actions\BuildTopCampaignStudioQueryAction;
use Capell\CampaignStudio\Data\Dashboard\CampaignConversionSummaryData;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Support\Collection;
use Override;

final class TopCampaignStudioFilamentWidget extends TableWidget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;
    use HasDashboardDateRange;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'top_campaign-studio';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = ['lg' => 1];

    protected static ?int $sort = 21;

    #[Override]
    public function table(Table $table): Table
    {
        return $table
            ->records(fn (): Collection => $this->records())
            ->heading(__('capell-campaign-studio::widgets.top_campaign-studio'))
            ->paginated(false)
            ->columns([
                TextColumn::make('campaign')
                    ->label(__('capell-campaign-studio::widgets.campaign')),
                TextColumn::make('visits')
                    ->label(__('capell-campaign-studio::widgets.visits'))
                    ->numeric(),
                TextColumn::make('conversions')
                    ->label(__('capell-campaign-studio::widgets.conversions'))
                    ->numeric(),
                TextColumn::make('conversion_rate')
                    ->label(__('capell-campaign-studio::widgets.conversion_rate')),
            ]);
    }

    /**
     * @return Collection<int, array{id: int, campaign: string, visits: int, conversions: int, conversion_rate: string}>
     */
    private function records(): Collection
    {
        [$rangeStart, $rangeEnd] = $this->getDashboardDateRange();

        return BuildTopCampaignStudioQueryAction::run(5, $rangeStart, $rangeEnd)
            ->map(fn (CampaignConversionSummaryData $summary): array => [
                'id' => $summary->campaignGroupId,
                'campaign' => $summary->campaignName,
                'visits' => $summary->visits,
                'conversions' => $summary->conversions,
                'conversion_rate' => $summary->conversionRate . '%',
            ]);
    }
}
