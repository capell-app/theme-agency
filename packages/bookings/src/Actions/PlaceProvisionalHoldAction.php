<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Enums\ConfirmationPolicyEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequestData $appointmentRequestData, ?CarbonImmutable $expiresAt = null)
 */
class PlaceProvisionalHoldAction
{
    use AsAction;

    public function handle(AppointmentRequestData $appointmentRequestData, ?CarbonImmutable $expiresAt = null): AppointmentRequest
    {
        return DB::transaction(function () use ($appointmentRequestData, $expiresAt): AppointmentRequest {
            $holdExpiryConfig = config('capell-bookings.hold_expiry_minutes', 30);
            $holdExpiryMinutes = is_numeric($holdExpiryConfig) ? (int) $holdExpiryConfig : 30;
            $holdExpiresAt = $expiresAt ?? CarbonImmutable::now()->addMinutes($holdExpiryMinutes);

            $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
                serviceId: $appointmentRequestData->serviceId,
                requestedStartsAt: $appointmentRequestData->requestedStartsAt,
                timezone: $appointmentRequestData->timezone,
                customerName: $appointmentRequestData->customerName,
                customerEmail: $appointmentRequestData->customerEmail,
                requestedEndsAt: $appointmentRequestData->requestedEndsAt,
                siteId: $appointmentRequestData->siteId,
                portalAccountId: $appointmentRequestData->portalAccountId,
                lessonSeriesId: $appointmentRequestData->lessonSeriesId,
                seriesOccurrenceDate: $appointmentRequestData->seriesOccurrenceDate,
                confirmationPolicy: $appointmentRequestData->confirmationPolicy === ConfirmationPolicyEnum::Manual
                    ? ConfirmationPolicyEnum::SelfConfirmLink
                    : $appointmentRequestData->confirmationPolicy,
                holdExpiresAt: $holdExpiresAt,
                offeredWindowStartsAt: $appointmentRequestData->offeredWindowStartsAt,
                offeredWindowEndsAt: $appointmentRequestData->offeredWindowEndsAt,
                isTimePinned: $appointmentRequestData->isTimePinned,
                staffMemberId: $appointmentRequestData->staffMemberId,
                locationId: $appointmentRequestData->locationId,
                customerPhone: $appointmentRequestData->customerPhone,
                notes: $appointmentRequestData->notes,
                source: $appointmentRequestData->source,
                payload: $appointmentRequestData->payload,
                reminderPreferences: $appointmentRequestData->reminderPreferences,
            ));

            $appointmentRequest->forceFill([
                'status' => AppointmentRequestStatusEnum::Provisional,
                'hold_expires_at' => $holdExpiresAt,
            ])->save();

            return $appointmentRequest->refresh();
        });
    }
}
