<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Contracts\BookingsAiAdvisor;
use Capell\Bookings\Models\AppointmentRequest;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static string run(AppointmentRequest $appointmentRequest)
 */
class GenerateReviewPromptAction
{
    use AsAction;

    public function handle(AppointmentRequest $appointmentRequest): string
    {
        $advice = app(BookingsAiAdvisor::class)->advise('review_prompt', [
            'appointment_request_id' => $appointmentRequest->getKey(),
            'service' => $appointmentRequest->service->name,
        ]);
        $body = $advice['body'] ?? null;

        return is_string($body) ? $body : __('capell-bookings::review.request_body');
    }
}
