<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingChangeProposalParty;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(BookingChangeProposalParty $proposalParty, ?CarbonImmutable $expiresAt = null)
 */
class IssueBookingChangeProposalTokenAction
{
    use AsAction;

    public function handle(BookingChangeProposalParty $proposalParty, ?CarbonImmutable $expiresAt = null): string
    {
        $token = Str::random(48);
        $tokenExpiresAt = $expiresAt ?? CarbonImmutable::now()->addDays(2);

        $proposalParty->forceFill([
            'token_hash' => hash('sha256', $token),
            'token_expires_at' => $tokenExpiresAt,
        ])->save();

        return URL::temporarySignedRoute(
            'capell-bookings.portal.proposal',
            $tokenExpiresAt,
            [
                'proposalParty' => $proposalParty->getKey(),
                'token' => $token,
            ],
        );
    }
}
