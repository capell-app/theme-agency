<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewParticipant run(BookingReviewParticipant $reviewParticipant, string $token, int $rating, ?string $response = null)
 */
class CaptureReviewParticipantResponseAction
{
    use AsAction;

    public function handle(
        BookingReviewParticipant $reviewParticipant,
        string $token,
        int $rating,
        ?string $response = null,
    ): BookingReviewParticipant {
        ResolveReviewParticipantTokenAction::run($reviewParticipant, $token);

        if ($rating < 1 || $rating > 5) {
            throw ValidationException::withMessages([
                'rating' => __('capell-bookings::validation.review_rating_range'),
            ]);
        }

        if ($reviewParticipant->status === BookingReviewParticipantStatusEnum::Completed) {
            throw ValidationException::withMessages([
                'review' => __('capell-bookings::validation.review_participant_completed'),
            ]);
        }

        $reviewParticipant->forceFill([
            'completed_at' => CarbonImmutable::now(),
            'rating' => $rating,
            'response' => $response,
            'status' => BookingReviewParticipantStatusEnum::Completed,
        ])->save();

        $reviewRequest = $reviewParticipant->reviewRequest;

        if ($reviewRequest instanceof BookingReviewRequest) {
            CompleteReviewLoopIfReadyAction::run($reviewRequest);
        }

        return $reviewParticipant->refresh();
    }
}
