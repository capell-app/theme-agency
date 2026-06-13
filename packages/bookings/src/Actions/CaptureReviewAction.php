<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewParticipantStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingReviewParticipant;
use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewRequest run(BookingReviewRequest $reviewRequest, int $rating, ?string $response = null)
 */
class CaptureReviewAction
{
    use AsAction;

    public function handle(BookingReviewRequest $reviewRequest, int $rating, ?string $response = null): BookingReviewRequest
    {
        if ($rating < 1 || $rating > 5) {
            throw ValidationException::withMessages([
                'rating' => __('capell-bookings::validation.review_rating_range'),
            ]);
        }

        $participant = $reviewRequest->participants()->first();

        if (! $participant instanceof BookingReviewParticipant) {
            $appointmentRequest = $reviewRequest->appointmentRequest;

            $participant = AddReviewParticipantAction::run(
                reviewRequest: $reviewRequest,
                role: 'customer',
                name: $appointmentRequest instanceof AppointmentRequest ? $appointmentRequest->customer_name : null,
                email: $appointmentRequest instanceof AppointmentRequest ? $appointmentRequest->customer_email : null,
                required: true,
                portalAccountId: $reviewRequest->portal_account_id,
            );
        }

        $participant->forceFill([
            'completed_at' => CarbonImmutable::now(),
            'rating' => $rating,
            'response' => $response,
            'status' => BookingReviewParticipantStatusEnum::Completed,
        ])->save();

        CompleteReviewLoopIfReadyAction::run($reviewRequest);

        return $reviewRequest->refresh();
    }
}
