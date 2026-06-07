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
            foreach ($this->limitedItems($provider->dashboardItemsFor($portalAccount), $this->perProviderLimit()) as $item) {
                $items[] = $item;
            }
        }

        usort($items, static fn (PortalDashboardItemData $firstItem, PortalDashboardItemData $secondItem): int => $secondItem->priority->value <=> $firstItem->priority->value
                ?: $firstItem->label <=> $secondItem->label);

        return array_slice($items, 0, $this->globalLimit());
    }

    private function perProviderLimit(): int
    {
        return $this->positiveIntegerConfig('dashboard_items_per_provider_limit', 6);
    }

    private function globalLimit(): int
    {
        return $this->positiveIntegerConfig('dashboard_items_limit', 12);
    }

    private function positiveIntegerConfig(string $key, int $fallback): int
    {
        $value = config('capell-customer-portal.' . $key, $fallback);

        if (! is_numeric($value)) {
            return $fallback;
        }

        $limit = (int) $value;

        return $limit > 0 ? $limit : $fallback;
    }

    /**
     * @param  iterable<PortalDashboardItemData>  $items
     * @return list<PortalDashboardItemData>
     */
    private function limitedItems(iterable $items, int $limit): array
    {
        $limitedItems = [];

        foreach ($items as $item) {
            $limitedItems[] = $item;

            if (count($limitedItems) >= $limit) {
                break;
            }
        }

        return $limitedItems;
    }
}
