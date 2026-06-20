<?php

declare(strict_types=1);

namespace Capell\CampaignStudio\Filament\Widgets;

use Capell\Admin\Contracts\CapellFilamentWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Admin\Filament\Concerns\HasDashboardDateRange;
use Capell\CampaignStudio\Actions\BuildCampaignOverviewStatsAction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

final class CampaignOverviewStatsFilamentWidget extends StatsOverviewWidget implements CapellFilamentWidgetContract
{
    use GatedByRoleAndSettings;
    use HasDashboardDateRange;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'campaign_overview';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 20;

    #[Override]
    protected function getStats(): array
    {
        [$rangeStart, $rangeEnd] = $this->getDashboardDateRange();
        $stats = BuildCampaignOverviewStatsAction::run($rangeStart, $rangeEnd);

        return [
            Stat::make(__('capell-campaign-studio::widgets.active_campaign-studio'), (string) $stats['active_campaign-studio']),
            Stat::make(__('capell-campaign-studio::widgets.conversions'), (string) $stats['conversions']),
            Stat::make(__('capell-campaign-studio::widgets.conversion_rate'), $stats['conversion_rate'] . '%'),
        ];
    }
}
