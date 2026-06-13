<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(PortalAccount $portalAccount, ?CarbonImmutable $expiresAt = null)
 */
class CreatePortalLessonsUrlAction
{
    use AsAction;

    public function handle(PortalAccount $portalAccount, ?CarbonImmutable $expiresAt = null): string
    {
        return URL::temporarySignedRoute(
            'capell-bookings.portal.lessons',
            $expiresAt ?? CarbonImmutable::now()->addDays(14),
            ['portalToken' => CreatePortalAccessTokenAction::run($portalAccount)],
        );
    }
}
