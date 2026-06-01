<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers\Concerns;

use Capell\CustomerPortal\Actions\ResolveAuthenticatedPortalAccountAction;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;

trait ResolvesPortalAccount
{
    private function portalAccount(Request $request): PortalAccount
    {
        $user = $request->user();

        abort_unless($user instanceof Authenticatable, 403);

        return ResolveAuthenticatedPortalAccountAction::run($user);
    }
}
