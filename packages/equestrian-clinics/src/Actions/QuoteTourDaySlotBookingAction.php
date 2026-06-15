<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Actions;

use Capell\EquestrianClinics\Data\EquestrianBookingQuoteData;
use Capell\EquestrianClinics\Enums\EquestrianPaymentFeeModeEnum;
use Capell\EquestrianClinics\Models\EquestrianTourDaySlot;
use Capell\Payments\Enums\PaymentProvider;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static EquestrianBookingQuoteData run(EquestrianTourDaySlot $slot, PaymentProvider $provider, EquestrianPaymentFeeModeEnum $feeMode = EquestrianPaymentFeeModeEnum::Universal, int $travelFeePence = 0, int $addOnsPence = 0, int $creditAppliedPence = 0, int $methodFeePence = 0, bool $methodFeeLegalAcknowledged = false)
 */
final class QuoteTourDaySlotBookingAction
{
    use AsAction;

    public function handle(
        EquestrianTourDaySlot $slot,
        PaymentProvider $provider,
        EquestrianPaymentFeeModeEnum $feeMode = EquestrianPaymentFeeModeEnum::Universal,
        int $travelFeePence = 0,
        int $addOnsPence = 0,
        int $creditAppliedPence = 0,
        int $methodFeePence = 0,
        bool $methodFeeLegalAcknowledged = false,
    ): EquestrianBookingQuoteData {
        $requiresMethodFeeAcknowledgement = (bool) config('capell-equestrian-clinics.method_fee_legal_acknowledgement_required', true);

        if ($feeMode === EquestrianPaymentFeeModeEnum::MethodSpecific && $requiresMethodFeeAcknowledgement && ! $methodFeeLegalAcknowledged) {
            throw ValidationException::withMessages([
                'payment_fee_mode' => __('capell-equestrian-clinics::validation.method_fee_legal_acknowledgement_required'),
            ]);
        }

        $configuredUniversalFeePence = config('capell-equestrian-clinics.universal_booking_fee_pence', 0);
        $universalBookingFeePence = $feeMode === EquestrianPaymentFeeModeEnum::Universal && is_int($configuredUniversalFeePence)
            ? $configuredUniversalFeePence
            : 0;
        $appliedMethodFeePence = $feeMode === EquestrianPaymentFeeModeEnum::MethodSpecific ? $methodFeePence : 0;
        $subtotalPence = $slot->price_pence + $universalBookingFeePence + $travelFeePence + $addOnsPence + $appliedMethodFeePence;
        $totalPence = max(0, $subtotalPence - $creditAppliedPence);

        return new EquestrianBookingQuoteData(
            basePricePence: $slot->price_pence,
            universalBookingFeePence: $universalBookingFeePence,
            travelFeePence: $travelFeePence,
            addOnsPence: $addOnsPence,
            methodFeePence: $appliedMethodFeePence,
            creditAppliedPence: min($creditAppliedPence, $subtotalPence),
            totalPence: $totalPence,
            provider: $provider,
            feeMode: $feeMode,
            methodFeeLegalAcknowledgementRequired: $feeMode === EquestrianPaymentFeeModeEnum::MethodSpecific && $requiresMethodFeeAcknowledgement,
        );
    }
}
