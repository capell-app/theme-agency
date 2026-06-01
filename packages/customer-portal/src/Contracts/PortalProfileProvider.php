<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Contracts;

use Capell\CustomerPortal\Data\PortalProfileData;
use Capell\CustomerPortal\Models\PortalAccount;

interface PortalProfileProvider
{
    public function profileFor(PortalAccount $portalAccount): PortalProfileData;
}
