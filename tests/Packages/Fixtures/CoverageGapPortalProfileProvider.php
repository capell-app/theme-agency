<?php

declare(strict_types=1);

namespace Capell\Tests\Packages\Fixtures;

use Capell\CustomerPortal\Contracts\PortalProfileProvider;
use Capell\CustomerPortal\Data\PortalProfileData;
use Capell\CustomerPortal\Enums\PortalAccountStatus;
use Capell\CustomerPortal\Models\PortalAccount;

final class CoverageGapPortalProfileProvider implements PortalProfileProvider
{
    public function profileFor(PortalAccount $portalAccount): PortalProfileData
    {
        return new PortalProfileData(
            accountId: 1,
            siteId: 1,
            email: 'container@example.test',
            displayName: 'Container',
            status: PortalAccountStatus::Active,
        );
    }
}
