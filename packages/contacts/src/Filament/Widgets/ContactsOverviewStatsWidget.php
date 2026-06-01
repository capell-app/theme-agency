<?php

declare(strict_types=1);

namespace Capell\Contacts\Filament\Widgets;

use Capell\Admin\Contracts\CapellWidgetContract;
use Capell\Admin\Filament\Concerns\GatedByRoleAndSettings;
use Capell\Contacts\Actions\BuildContactsOverviewStatsAction;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Override;

final class ContactsOverviewStatsWidget extends StatsOverviewWidget implements CapellWidgetContract
{
    use GatedByRoleAndSettings;

    /** @var list<string> */
    protected static array $rolesConfigKeys = ['admin', 'super_admin'];

    protected static string $settingsKey = 'contacts_overview';

    /** @var int|string|array<string, int|null> */
    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 30;

    /**
     * @return array<int, Stat>
     */
    #[Override]
    protected function getStats(): array
    {
        $stats = BuildContactsOverviewStatsAction::run();

        return [
            Stat::make(__('capell-contacts::generic.widgets.contacts'), number_format($stats['contacts'])),
            Stat::make(__('capell-contacts::generic.widgets.organisations'), number_format($stats['organisations'])),
            Stat::make(__('capell-contacts::generic.widgets.open_leads'), number_format($stats['open_leads'])),
            Stat::make(__('capell-contacts::generic.widgets.activities'), number_format($stats['activities'])),
        ];
    }
}
