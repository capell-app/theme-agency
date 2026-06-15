<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\ResolvePortalAccessTokenAction;
use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Models\MessagingConsent;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

final class ShowMessagingConsentController
{
    public function __invoke(Request $request, string $portalToken): View
    {
        abort_unless($request->hasValidSignature(), 403);
        $signatureExpiresAt = filter_var($request->query('expires'), FILTER_VALIDATE_INT);
        abort_unless($signatureExpiresAt !== false, 403);

        $resolved = ResolvePortalAccessTokenAction::run($portalToken);
        /** @var PortalAccount $portalAccount */
        $portalAccount = $resolved['portal_account'];
        $siteId = $resolved['site_id'];

        return view('capell-bookings::portal.consent', [
            'channels' => BookingMessageChannelEnum::cases(),
            'consents' => MessagingConsent::query()
                ->where('site_id', $siteId)
                ->where('portal_account_id', $portalAccount->getKey())
                ->get()
                ->keyBy(static fn (MessagingConsent $consent): string => $consent->channel->value),
            'portalAccount' => $portalAccount,
            'portalToken' => $portalToken,
            'signatureExpiresAt' => CarbonImmutable::createFromTimestampUTC($signatureExpiresAt),
        ]);
    }
}
