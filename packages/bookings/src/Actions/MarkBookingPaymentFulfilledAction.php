<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequest $appointmentRequest, string $providerReference, int $amountPence, ?string $checkoutSessionId = null)
 */
class MarkBookingPaymentFulfilledAction
{
    use AsAction;

    public function handle(
        AppointmentRequest $appointmentRequest,
        string $providerReference,
        int $amountPence,
        ?string $checkoutSessionId = null,
    ): AppointmentRequest {
        $requiredAmountPence = (int) ($appointmentRequest->payment_required_amount_pence ?? 0);

        if ($requiredAmountPence > 0 && $amountPence < $requiredAmountPence) {
            throw ValidationException::withMessages([
                'amount_pence' => __('capell-bookings::validation.payment_amount_insufficient'),
            ]);
        }

        $appointmentRequest->forceFill([
            'payment_checkout_session_id' => $checkoutSessionId ?? $appointmentRequest->payment_checkout_session_id,
            'payment_provider_reference' => $providerReference,
            'payment_confirmed_at' => CarbonImmutable::now(),
        ])->save();

        return $appointmentRequest->refresh();
    }
}
