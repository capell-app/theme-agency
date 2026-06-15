<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\BookingOwnerPromptStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingOwnerPrompt;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingOwnerPrompt run(AppointmentRequest $appointmentRequest, string $reason, ?CarbonImmutable $expiresAt = null)
 */
class ProposeWeatherCancellationAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        string $reason,
        ?CarbonImmutable $expiresAt = null,
    ): BookingOwnerPrompt {
        /** @var BookingOwnerPrompt $prompt */
        $prompt = BookingOwnerPrompt::query()->create([
            'body' => __('capell-bookings::admin.owner_prompts.weather_cancellation_body', [
                'customer' => $appointmentRequest->customer_name,
                'reason' => $reason,
            ]),
            'context' => [
                'appointment_request_id' => $appointmentRequest->getKey(),
                'reason' => $reason,
            ],
            'expires_at' => $expiresAt,
            'site_id' => $appointmentRequest->site_id,
            'status' => BookingOwnerPromptStatusEnum::Proposed,
            'title' => __('capell-bookings::admin.owner_prompts.weather_cancellation_title'),
            'type' => 'weather_cancellation',
        ]);

        return $prompt;
    }
}
