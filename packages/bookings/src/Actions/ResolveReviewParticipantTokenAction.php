<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingReviewParticipant;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewParticipant run(BookingReviewParticipant $reviewParticipant, string $token)
 */
class ResolveReviewParticipantTokenAction
{
    use AsAction;

    public function handle(BookingReviewParticipant $reviewParticipant, string $token): BookingReviewParticipant
    {
        if (
            $reviewParticipant->token_hash === null
            || ! hash_equals($reviewParticipant->token_hash, hash('sha256', $token))
            || ($reviewParticipant->token_expires_at !== null && $reviewParticipant->token_expires_at->lessThanOrEqualTo(CarbonImmutable::now()))
        ) {
            throw ValidationException::withMessages([
                'token' => __('capell-bookings::validation.review_participant_token_invalid'),
            ]);
        }

        return $reviewParticipant;
    }
}
