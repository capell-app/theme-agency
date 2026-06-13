<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\URL;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(BookingReviewRequest $reviewRequest, ?CarbonImmutable $expiresAt = null)
 */
class CreateReviewRequestUrlAction
{
    use AsAction;

    public function handle(BookingReviewRequest $reviewRequest, ?CarbonImmutable $expiresAt = null): string
    {
        return URL::temporarySignedRoute(
            'capell-bookings.portal.review',
            $expiresAt ?? CarbonImmutable::now()->addDays(14),
            ['reviewRequest' => $reviewRequest->getKey()],
        );
    }
}
