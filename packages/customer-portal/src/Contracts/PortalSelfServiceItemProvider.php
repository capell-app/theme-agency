<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Contracts;

use Capell\CustomerPortal\Data\PortalSelfServiceItemData;
use Capell\CustomerPortal\Models\PortalAccount;

interface PortalSelfServiceItemProvider
{
    /**
     * @return iterable<PortalSelfServiceItemData>
     */
    public function selfServiceItemsFor(PortalAccount $portalAccount): iterable;
}
