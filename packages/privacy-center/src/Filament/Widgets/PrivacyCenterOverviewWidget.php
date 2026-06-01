<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\PrivacyCenter\Actions\BuildPrivacyCenterOverviewStatsAction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

final class PrivacyCenterOverviewWidget extends StatsOverviewWidget implements CapellWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'privacy_center_overview';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 35;

    /**
     * @return array<int, Stat>
     */
    #[Override]
    protected function getStats(): array
    {
        $stats = BuildPrivacyCenterOverviewStatsAction::run();

        return [
            Stat::make(__('capell-privacy-center::privacy.admin.widgets.consent_records'), number_format($stats['consent_records'])),
            Stat::make(__('capell-privacy-center::privacy.admin.widgets.granted_consents'), number_format($stats['granted_consents'])),
            Stat::make(__('capell-privacy-center::privacy.admin.widgets.open_privacy_requests'), number_format($stats['open_privacy_requests'])),
            Stat::make(__('capell-privacy-center::privacy.admin.widgets.active_retention_rules'), number_format($stats['active_retention_rules'])),
        ];
    }
}
