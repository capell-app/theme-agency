<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Actions;

use Capell\CustomerPortal\Data\PortalPreferencesData;
use Capell\CustomerPortal\Models\PortalAccount;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static PortalAccount run(PortalAccount $portalAccount, PortalPreferencesData $preferencesData)
 */
class UpdatePortalPreferencesAction
{
    use AsAction;

    public function handle(PortalAccount $portalAccount, PortalPreferencesData $preferencesData): PortalAccount
    {
        $portalAccount->forceFill([
            'preferences' => $preferencesData->replace
                ? $preferencesData->values
                : array_replace($portalAccount->preferences ?? [], $preferencesData->values),
        ])->save();

        return $portalAccount->refresh();
    }
}
