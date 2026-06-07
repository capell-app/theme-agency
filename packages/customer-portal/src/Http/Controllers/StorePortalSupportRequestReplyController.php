<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers;

use Capell\CustomerPortal\Actions\AddSupportRequestReplyAction;
use Capell\CustomerPortal\Http\Controllers\Concerns\ResolvesPortalAccount;
use Capell\CustomerPortal\Models\PortalSupportRequest;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class StorePortalSupportRequestReplyController
{
    use ResolvesPortalAccount;

    public function __invoke(Request $request, int $supportRequest): RedirectResponse
    {
        $portalAccount = $this->portalAccount($request);
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        /** @var PortalSupportRequest $portalSupportRequest */
        $portalSupportRequest = $portalAccount->supportRequests()->findOrFail($supportRequest);
        $author = $request->user();

        AddSupportRequestReplyAction::run(
            supportRequest: $portalSupportRequest,
            message: (string) $validated['message'],
            senderType: 'customer',
            author: $author instanceof Model ? $author : null,
        );

        return to_route('capell-customer-portal.dashboard')
            ->with('customer_portal_status', __('capell-customer-portal::generic.frontend.support_reply_submitted'));
    }
}
