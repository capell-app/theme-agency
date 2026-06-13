<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
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

        $reviewRequest->forceFill([
            'status' => BookingReviewRequestStatusEnum::Completed,
            'rating' => $rating,
            'response' => $response,
            'completed_at' => CarbonImmutable::now(),
        ])->save();

        return $reviewRequest->refresh();
    }
}
