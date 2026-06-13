<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(BookingReviewRequest $reviewRequest, ?CarbonImmutable $expiresAt = null)
 */
class CreateReviewRequestUrlAction
{
    use AsAction;

    public function handle(BookingReviewRequest $reviewRequest, ?CarbonImmutable $expiresAt = null): string
    {
        $token = Str::random(48);
        $tokenExpiresAt = $expiresAt ?? CarbonImmutable::now()->addDays(14);

        $reviewRequest->forceFill([
            'token_hash' => hash('sha256', $token),
            'token_expires_at' => $tokenExpiresAt,
        ])->save();

        return URL::temporarySignedRoute(
            'capell-bookings.portal.review',
            $tokenExpiresAt,
            ['token' => $token],
        );
    }
}
