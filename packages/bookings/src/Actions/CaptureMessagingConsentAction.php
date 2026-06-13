<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\MessagingConsentStatusEnum;
use Capell\Bookings\Models\MessagingConsent;
use Capell\CustomerPortal\Models\PortalAccount;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static MessagingConsent run(PortalAccount $portalAccount, BookingMessageChannelEnum $channel, bool $granted, ?string $recipient = null, array<string, mixed> $evidence = [])
 */
class CaptureMessagingConsentAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $evidence
     */
    public function handle(
        PortalAccount $portalAccount,
        BookingMessageChannelEnum $channel,
        bool $granted,
        ?string $recipient = null,
        array $evidence = [],
    ): MessagingConsent {
        /** @var MessagingConsent $consent */
        $consent = MessagingConsent::query()->updateOrCreate(
            [
                'site_id' => $portalAccount->site_id,
                'portal_account_id' => $portalAccount->getKey(),
                'channel' => $channel,
            ],
            [
                'recipient' => $recipient ?? $portalAccount->email,
                'status' => $granted ? MessagingConsentStatusEnum::Granted : MessagingConsentStatusEnum::Revoked,
                'evidence' => $evidence,
                'consented_at' => $granted ? CarbonImmutable::now() : null,
                'revoked_at' => $granted ? null : CarbonImmutable::now(),
            ],
        );

        return $consent;
    }
}
