<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Support\PortalSelfServiceItemRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array<int, PortalSelfServiceItemData> run(PortalAccount $portalAccount)
 */
final class ResolvePortalSelfServiceItemsAction
{
    use AsAction;

    /**
     * @return list<PortalSelfServiceItemData>
     */
    public function handle(PortalAccount $portalAccount): array
    {
        /** @var PortalSelfServiceItemRegistry $registry */
        $registry = resolve(PortalSelfServiceItemRegistry::class);
        $items = [];

        foreach ($registry->providers() as $provider) {
            foreach ($this->limitedItems($provider->selfServiceItemsFor($portalAccount), $this->perProviderLimit()) as $item) {
                $items[] = $item;
            }
        }

        usort($items, static function (PortalSelfServiceItemData $firstItem, PortalSelfServiceItemData $secondItem): int {
            $firstTimestamp = $firstItem->occurredAt?->getTimestamp() ?? 0;
            $secondTimestamp = $secondItem->occurredAt?->getTimestamp() ?? 0;

            return $secondTimestamp <=> $firstTimestamp
                ?: $firstItem->type->value <=> $secondItem->type->value
                ?: $firstItem->label <=> $secondItem->label;
        });

        return array_slice($items, 0, $this->globalLimit());
    }

    private function perProviderLimit(): int
    {
        return $this->positiveIntegerConfig('self_service_items_per_provider_limit', 8);
    }

    private function globalLimit(): int
    {
        return $this->positiveIntegerConfig('self_service_items_limit', 20);
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
     * @param  iterable<PortalSelfServiceItemData>  $items
     * @return list<PortalSelfServiceItemData>
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
