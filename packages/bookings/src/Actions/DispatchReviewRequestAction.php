<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingMessageChannelEnum;
use Capell\Bookings\Enums\BookingReviewRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingReviewRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingReviewRequest run(BookingReviewRequest $reviewRequest)
 */
class DispatchReviewRequestAction
{
    use AsAction;

    public function handle(BookingReviewRequest $reviewRequest): BookingReviewRequest
    {
        $appointmentRequest = $reviewRequest->appointmentRequest;

        if (! $appointmentRequest instanceof AppointmentRequest) {
            throw ValidationException::withMessages([
                'appointment_request_id' => __('capell-bookings::validation.appointment_not_found'),
            ]);
        }

        DispatchBookingMessageAction::run(
            appointmentRequest: $appointmentRequest,
            channel: BookingMessageChannelEnum::Email,
            type: 'review_request',
            body: __('capell-bookings::review.request_body'),
            subject: __('capell-bookings::review.request_subject'),
        );

        $reviewRequest->forceFill([
            'status' => BookingReviewRequestStatusEnum::Sent,
            'sent_at' => CarbonImmutable::now(),
        ])->save();

        return $reviewRequest->refresh();
    }
}
