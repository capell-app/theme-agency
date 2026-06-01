<?php

declare(strict_types=1);

namespace Capell\Payments\Http\Controllers;

use Capell\CustomerPortal\Actions\ResolveAuthenticatedPortalAccountAction;
use Capell\Payments\Actions\CreateBillingPortalSessionAction;
use Capell\Payments\Actions\ResolvePortalPaymentCustomerAction;
use Capell\Payments\Data\CreateBillingPortalSessionData;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

final class CreatePortalBillingSessionController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user instanceof Authenticatable, 403);

        $portalAccount = ResolveAuthenticatedPortalAccountAction::run($user);
        $paymentCustomer = ResolvePortalPaymentCustomerAction::run($portalAccount);

        abort_if($paymentCustomer === null || $paymentCustomer->provider_customer_id === null, 404);

        $session = CreateBillingPortalSessionAction::run(new CreateBillingPortalSessionData(
            providerCustomerId: $paymentCustomer->provider_customer_id,
            returnUrl: URL::route('capell-customer-portal.dashboard'),
        ));

        return redirect()->away($session->url);
    }
}
