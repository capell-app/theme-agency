<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalProfileData;
use Capell\CustomerPortal\Models\PortalAccount;
use Capell\CustomerPortal\Support\PortalProfileProviderRegistry;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PortalProfileData run(PortalAccount $portalAccount)
 */
final class ResolvePortalProfileAction
{
    use AsAction;

    public function handle(PortalAccount $portalAccount): PortalProfileData
    {
        /** @var PortalProfileProviderRegistry $registry */
        $registry = resolve(PortalProfileProviderRegistry::class);
        $profileData = PortalProfileData::fromAccount($portalAccount);

        foreach ($registry->providers() as $provider) {
            $providerProfileData = $provider->profileFor($portalAccount);

            $profileData = new PortalProfileData(
                accountId: (int) $portalAccount->getKey(),
                siteId: (int) $portalAccount->site_id,
                email: $providerProfileData->email ?? $profileData->email,
                displayName: $providerProfileData->displayName ?? $profileData->displayName,
                status: $portalAccount->status,
                profile: array_replace($profileData->profile, $providerProfileData->profile),
            );
        }

        return $profileData;
    }
}
