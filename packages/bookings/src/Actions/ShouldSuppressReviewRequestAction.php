<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static bool run(AppointmentRequest $appointmentRequest)
 */
class ShouldSuppressReviewRequestAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest): bool
    {
        $payload = is_array($appointmentRequest->payload) ? $appointmentRequest->payload : [];

        return $appointmentRequest->status === AppointmentRequestStatusEnum::NoShow
            || $appointmentRequest->status === AppointmentRequestStatusEnum::Cancelled
            || $appointmentRequest->completed_at === null
            || (bool) ($payload['suppress_review_request'] ?? false);
    }
}
