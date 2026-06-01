<?php

declare(strict_types=1);

namespace Capell\CustomerPortal\Http\Controllers;

use Capell\CustomerPortal\Actions\SubmitSupportRequestAction;
use Capell\CustomerPortal\Data\SupportRequestData;
use Capell\CustomerPortal\Enums\SupportRequestPriority;
use Capell\CustomerPortal\Http\Controllers\Concerns\ResolvesPortalAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class StorePortalSupportRequestController
{
    use ResolvesPortalAccount;

    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:5000'],
            'priority' => ['nullable', Rule::enum(SupportRequestPriority::class)],
        ]);

        SubmitSupportRequestAction::run(
            portalAccount: $this->portalAccount($request),
            supportRequestData: new SupportRequestData(
                subject: (string) $validated['subject'],
                message: (string) $validated['message'],
                priority: SupportRequestPriority::tryFrom((string) ($validated['priority'] ?? '')) ?? SupportRequestPriority::Normal,
                source: 'customer-portal',
                context: [
                    'path' => '/' . trim($request->path(), '/'),
                ],
            ),
        );

        return to_route('capell-customer-portal.dashboard')
            ->with('customer_portal_status', __('capell-customer-portal::generic.frontend.support_submitted'));
    }
}
