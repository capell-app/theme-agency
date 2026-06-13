<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Models\MessagingConsent;
use Capell\CustomerPortal\Models\PortalAccount;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowMessagingConsentController
{
    public function __invoke(Request $request, int $site, PortalAccount $portalAccount): View
    {
        abort_unless($request->hasValidSignature(), 403);
        abort_unless($portalAccount->site_id === $site, 404);

        return view('capell-bookings::portal.consent', [
            'channels' => BookingMessageChannelEnum::cases(),
            'consents' => MessagingConsent::query()
                ->where('site_id', $site)
                ->where('portal_account_id', $portalAccount->getKey())
                ->get()
                ->keyBy(static fn (MessagingConsent $consent): string => $consent->channel->value),
            'portalAccount' => $portalAccount,
            'siteId' => $site,
        ]);
    }
}
