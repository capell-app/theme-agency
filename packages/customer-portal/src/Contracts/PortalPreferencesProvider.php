<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Contracts;

use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Models\PortalAccount;

interface PortalPreferencesProvider
{
    public function preferencesFor(PortalAccount $portalAccount): PortalPreferencesData;
}
