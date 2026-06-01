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
            foreach ($provider->selfServiceItemsFor($portalAccount) as $item) {
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

        return $items;
    }
}
