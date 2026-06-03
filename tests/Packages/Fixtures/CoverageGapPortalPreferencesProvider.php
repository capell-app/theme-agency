<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\CustomerPortal\Contracts\PortalPreferencesProvider;
use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Models\PortalAccount;

final class CoverageGapPortalPreferencesProvider implements PortalPreferencesProvider
{
    public function preferencesFor(PortalAccount $portalAccount): PortalPreferencesData
    {
        return new PortalPreferencesData(['timezone' => 'Europe/London']);
    }
}
