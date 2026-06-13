<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingReviewParticipant;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(BookingReviewParticipant $reviewParticipant, ?CarbonImmutable $expiresAt = null)
 */
class IssueReviewParticipantUrlAction
{
    use AsAction;

    public function handle(BookingReviewParticipant $reviewParticipant, ?CarbonImmutable $expiresAt = null): string
    {
        $token = Str::random(48);
        $tokenExpiresAt = $expiresAt ?? CarbonImmutable::now()->addDays(14);

        $reviewParticipant->forceFill([
            'token_hash' => hash('sha256', $token),
            'token_expires_at' => $tokenExpiresAt,
        ])->save();

        return URL::temporarySignedRoute(
            'capell-bookings.portal.review-participant',
            $tokenExpiresAt,
            [
                'reviewParticipant' => $reviewParticipant->getKey(),
                'token' => $token,
            ],
        );
    }
}
