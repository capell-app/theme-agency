<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingReviewParticipant;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewParticipant run(BookingReviewParticipant|string $reviewParticipant, ?string $token = null)
 */
class ResolveReviewParticipantTokenAction
{
    use AsAction;

    public function handle(BookingReviewParticipant|string $reviewParticipant, ?string $token = null): BookingReviewParticipant
    {
        if (is_string($reviewParticipant)) {
            $token = $reviewParticipant;

            /** @var BookingReviewParticipant|null $matchedReviewParticipant */
            $matchedReviewParticipant = BookingReviewParticipant::query()
                ->where('token_hash', hash('sha256', $token))
                ->first();

            if (! $matchedReviewParticipant instanceof BookingReviewParticipant) {
                throw $this->invalidToken();
            }

            $reviewParticipant = $matchedReviewParticipant;
        }

        if (
            $token === null
            || $token === ''
            || $reviewParticipant->token_hash === null
            || ! hash_equals($reviewParticipant->token_hash, hash('sha256', $token))
            || ($reviewParticipant->token_expires_at !== null && $reviewParticipant->token_expires_at->lessThanOrEqualTo(CarbonImmutable::now()))
        ) {
            throw $this->invalidToken();
        }

        return $reviewParticipant;
    }

    private function invalidToken(): ValidationException
    {
        return ValidationException::withMessages([
            'token' => __('capell-bookings::validation.review_participant_token_invalid'),
        ]);
    }
}
