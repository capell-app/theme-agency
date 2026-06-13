<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingReviewRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static int run(AppointmentRequest $appointmentRequest)
 */
class SuppressReviewRequestsAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest): int
    {
        return BookingReviewRequest::query()
            ->where('appointment_request_id', $appointmentRequest->getKey())
            ->whereIn('status', [
                BookingReviewRequestStatusEnum::Scheduled->value,
                BookingReviewRequestStatusEnum::Sent->value,
            ])
            ->update(['status' => BookingReviewRequestStatusEnum::Suppressed->value]);
    }
}
