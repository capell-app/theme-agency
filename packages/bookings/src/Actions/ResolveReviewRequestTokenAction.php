<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewRequest run(string $token)
 */
class ResolveReviewRequestTokenAction
{
    use AsAction;

    public function handle(string $token): BookingReviewRequest
    {
        /** @var BookingReviewRequest|null $reviewRequest */
        $reviewRequest = BookingReviewRequest::query()
            ->where('token_hash', hash('sha256', $token))
            ->first();

        if (
            ! $reviewRequest instanceof BookingReviewRequest
            || $reviewRequest->token_hash === null
            || ($reviewRequest->token_expires_at !== null && $reviewRequest->token_expires_at->lessThanOrEqualTo(CarbonImmutable::now()))
        ) {
            throw ValidationException::withMessages([
                'token' => __('capell-bookings::validation.review_request_token_invalid'),
            ]);
        }

        return $reviewRequest;
    }
}
