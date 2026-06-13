<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\CaptureMessagingConsentAction;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

final class UpdateMessagingConsentController
{
    public function __invoke(Request $request, int $site, PortalAccount $portalAccount): RedirectResponse
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($portalAccount->site_id === $site, 404);

        $validated = $request->validate([
            'channel' => ['required', Rule::enum(BookingMessageChannelEnum::class)],
            'granted' => ['required', 'boolean'],
            'recipient' => ['nullable', 'string', 'max:255'],
        ]);

        CaptureMessagingConsentAction::run(
            portalAccount: $portalAccount,
            channel: BookingMessageChannelEnum::from((string) $validated['channel']),
            granted: (bool) $validated['granted'],
            recipient: is_string($validated['recipient'] ?? null) ? $validated['recipient'] : null,
            evidence: [
                'source' => 'signed-bookings-consent',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ],
        );

        return back()->with('booking_portal_status', __('capell-bookings::portal.consent_saved'));
    }
}
