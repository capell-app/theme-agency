<?php

declare(strict_types=1);

namespace Capell\PublishingStudio\Contracts;

use Capell\PublishingStudio\Data\ReleaseWorkspaceItemData;
use Capell\PublishingStudio\Models\Workspace;

interface CountableReleaseWorkspaceItemContributor extends ReleaseWorkspaceItemContributor
{
    public function countFor(Workspace $workspace): int;

    /**
     * @return list<ReleaseWorkspaceItemData>
     */
    public function limitedItemsFor(Workspace $workspace, ?int $limit = null): array;
}
