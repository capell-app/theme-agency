<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Support\PortalDashboardItemRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, PortalDashboardItemData> run(PortalAccount $portalAccount)
 */
class ResolvePortalDashboardItemsAction
{
    use AsAction;

    /**
     * @return list<PortalDashboardItemData>
     */
    public function handle(PortalAccount $portalAccount): array
    {
        /** @var PortalDashboardItemRegistry $registry */
        $registry = resolve(PortalDashboardItemRegistry::class);
        $items = [];

        foreach ($registry->providers() as $provider) {
            foreach ($provider->dashboardItemsFor($portalAccount) as $item) {
                $items[] = $item;
            }
        }

        usort($items, static fn (PortalDashboardItemData $firstItem, PortalDashboardItemData $secondItem): int => $secondItem->priority->value <=> $firstItem->priority->value
                ?: $firstItem->label <=> $secondItem->label);

        return $items;
    }
}
