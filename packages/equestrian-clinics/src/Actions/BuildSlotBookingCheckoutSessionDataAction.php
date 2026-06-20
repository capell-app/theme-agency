<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Enums\EquestrianPaymentStatusEnum;
use Capell\EquestrianClinics\Enums\EquestrianSlotBookingStatusEnum;
use Capell\EquestrianClinics\Models\EquestrianSlotBooking;
use Capell\Payments\Data\CheckoutLineItemData;
use Capell\Payments\Data\CreateCheckoutSessionData;
use Capell\Payments\Enums\CheckoutMode;
use Capell\Payments\Enums\PaymentPurpose;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static CreateCheckoutSessionData run(EquestrianSlotBooking $booking, string $successUrl, string $cancelUrl, string $currency = 'gbp', ?CarbonImmutable $now = null)
 */
final class BuildSlotBookingCheckoutSessionDataAction
{
    use AsAction;

    public function handle(
        EquestrianSlotBooking $booking,
        string $successUrl,
        string $cancelUrl,
        string $currency = 'gbp',
        ?CarbonImmutable $now = null,
    ): CreateCheckoutSessionData {
        $now ??= CarbonImmutable::now();
        $booking->loadMissing('slot.tourDay', 'riderProfile', 'horseProfile');

        if (! $booking->isActiveHold($now) || $booking->payment_status !== EquestrianPaymentStatusEnum::Pending || $booking->payment_provider === null || $booking->cash_payment) {
            throw ValidationException::withMessages([
                'booking_id' => __('capell-equestrian-clinics::validation.checkout_handoff_not_available'),
            ]);
        }

        if (! $this->isAbsoluteHttpUrl($successUrl) || ! $this->isAbsoluteHttpUrl($cancelUrl)) {
            throw ValidationException::withMessages([
                'checkout_url' => __('capell-equestrian-clinics::validation.checkout_url_invalid'),
            ]);
        }

        return new CreateCheckoutSessionData(
            successUrl: $successUrl,
            cancelUrl: $cancelUrl,
            lineItems: [
                new CheckoutLineItemData(
                    name: $booking->slot->title . ' - ' . $booking->slot->tourDay->title,
                    amount: $booking->quoted_total_pence,
                    currency: strtolower($currency),
                    quantity: 1,
                    description: $booking->horseProfile === null
                        ? $booking->riderProfile->name
                        : $booking->riderProfile->name . ' with ' . $booking->horseProfile->name,
                    metadata: [
                        'equestrian_slot_booking_id' => $booking->getKey(),
                        'equestrian_tour_day_slot_id' => $booking->tour_day_slot_id,
                    ],
                ),
            ],
            purpose: PaymentPurpose::OneOff,
            mode: CheckoutMode::Payment,
            provider: $booking->payment_provider,
            siteId: $booking->slot->tourDay->site_id ?? $booking->riderProfile->site_id,
            customerEmail: $booking->riderProfile->email,
            customerName: $booking->riderProfile->name,
            payableType: EquestrianSlotBooking::class,
            payableId: (string) $booking->id,
            sourceType: 'capell-equestrian-clinics',
            sourceId: (string) $booking->slot->tourDay->id,
            referenceId: 'equestrian-slot-booking-' . $booking->id,
            metadata: [
                'package' => 'capell-app/equestrian-clinics',
                'equestrian_slot_booking_id' => $booking->getKey(),
                'equestrian_tour_day_id' => $booking->slot->tour_day_id,
                'equestrian_tour_day_slot_id' => $booking->tour_day_slot_id,
                'hold_expires_at' => $booking->hold_expires_at?->toIso8601String(),
                'payment_status' => EquestrianPaymentStatusEnum::Pending->value,
                'slot_booking_status' => EquestrianSlotBookingStatusEnum::Held->value,
            ],
        );
    }

    private function isAbsoluteHttpUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return $scheme === 'http' || $scheme === 'https';
    }
}
