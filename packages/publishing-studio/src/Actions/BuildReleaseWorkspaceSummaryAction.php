<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Actions;

use Capell\PublishingStudio\Contracts\CountableReleaseWorkspaceItemContributor;
use Capell\PublishingStudio\Contracts\ReleaseWorkspaceItemContributor;
use Capell\PublishingStudio\Data\ReleaseWorkspaceItemData;
use Capell\PublishingStudio\Data\ReleaseWorkspaceSummaryData;
use Capell\PublishingStudio\Models\Workspace;
use Capell\PublishingStudio\ReleaseWorkspaceItemRegistry;
use Lorisleiva\Actions\Concerns\AsObject;

final class BuildReleaseWorkspaceSummaryAction
{
    use AsObject;

    public function handle(Workspace $workspace, int $limit = 25): ReleaseWorkspaceSummaryData
    {
        $items = [];
        $itemCount = 0;
        $registry = resolve(ReleaseWorkspaceItemRegistry::class);

        foreach ($registry->contributors() as $contributorClass) {
            $contributor = resolve($contributorClass);

            if (! $contributor instanceof ReleaseWorkspaceItemContributor) {
                continue;
            }

            $remainingLimit = max(0, $limit - count($items));

            if ($contributor instanceof CountableReleaseWorkspaceItemContributor) {
                $itemCount += $contributor->countFor($workspace);
                $contributorItems = $remainingLimit > 0
                    ? $contributor->limitedItemsFor($workspace, $remainingLimit)
                    : [];
            } else {
                $contributorItems = $contributor->itemsFor($workspace);
            }

            foreach ($contributorItems as $item) {
                if (! $item instanceof ReleaseWorkspaceItemData) {
                    continue;
                }

                if (! $contributor instanceof CountableReleaseWorkspaceItemContributor) {
                    $itemCount++;
                }

                if (count($items) < $limit) {
                    $items[] = $item;
                }
            }
        }

        return new ReleaseWorkspaceSummaryData(
            workspaceId: (int) $workspace->getKey(),
            items: $items,
            itemCount: $itemCount,
        );
    }
}
