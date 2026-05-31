<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Contracts;

use Capell\CustomerPortal\Data\PortalDashboardItemData;
use Capell\CustomerPortal\Models\PortalAccount;

interface PortalDashboardItemProvider
{
    /**
     * @return iterable<PortalDashboardItemData>
     */
    public function dashboardItemsFor(PortalAccount $portalAccount): iterable;
}
