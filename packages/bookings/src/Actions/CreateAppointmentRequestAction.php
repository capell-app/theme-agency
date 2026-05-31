<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentRequest run(AppointmentRequestData $appointmentRequestData)
 */
class CreateAppointmentRequestAction
{
    use AsAction;

    public function handle(AppointmentRequestData $appointmentRequestData): AppointmentRequest
    {
        return DB::transaction(function () use ($appointmentRequestData): AppointmentRequest {
            $service = $this->activeService($appointmentRequestData->serviceId);
            $staffMember = $appointmentRequestData->staffMemberId === null
                ? null
                : $this->activeStaffMember($appointmentRequestData->staffMemberId);
            $location = $appointmentRequestData->locationId === null
                ? null
                : $this->activeLocation($appointmentRequestData->locationId);
            $requestedEndsAt = $appointmentRequestData->requestedEndsAt
                ?? $appointmentRequestData->requestedStartsAt->addMinutes($service->duration_minutes);

            $this->validateRequestWindow($appointmentRequestData->requestedStartsAt, $requestedEndsAt);
            $this->validateAvailability($appointmentRequestData, $requestedEndsAt);

            /** @var AppointmentRequest $appointmentRequest */
            $appointmentRequest = AppointmentRequest::query()->create([
                'service_id' => $service->getKey(),
                'staff_member_id' => $staffMember?->getKey(),
                'location_id' => $location?->getKey(),
                'status' => AppointmentRequestStatusEnum::Requested,
                'requested_starts_at' => $appointmentRequestData->requestedStartsAt,
                'requested_ends_at' => $requestedEndsAt,
                'timezone' => $appointmentRequestData->timezone,
                'customer_name' => $appointmentRequestData->customerName,
                'customer_email' => $appointmentRequestData->customerEmail,
                'customer_phone' => $appointmentRequestData->customerPhone,
                'notes' => $appointmentRequestData->notes,
                'source' => $appointmentRequestData->source,
                'calendar_uid' => (string) Str::uuid(),
                'reminder_preferences' => $appointmentRequestData->reminderPreferences,
                'payload' => $appointmentRequestData->payload,
                'requested_at' => CarbonImmutable::now(),
            ]);

            return $appointmentRequest;
        });
    }

    private function activeService(int $serviceId): BookingService
    {
        /** @var BookingService|null $service */
        $service = BookingService::query()->active()->find($serviceId);

        if ($service === null) {
            throw ValidationException::withMessages([
                'service_id' => __('capell-bookings::validation.service_unavailable'),
            ]);
        }

        return $service;
    }

    private function activeStaffMember(int $staffMemberId): BookingStaffMember
    {
        /** @var BookingStaffMember|null $staffMember */
        $staffMember = BookingStaffMember::query()->active()->find($staffMemberId);

        if ($staffMember === null) {
            throw ValidationException::withMessages([
                'staff_member_id' => __('capell-bookings::validation.staff_member_unavailable'),
            ]);
        }

        return $staffMember;
    }

    private function activeLocation(int $locationId): BookingLocation
    {
        /** @var BookingLocation|null $location */
        $location = BookingLocation::query()->active()->find($locationId);

        if ($location === null) {
            throw ValidationException::withMessages([
                'location_id' => __('capell-bookings::validation.location_unavailable'),
            ]);
        }

        return $location;
    }

    private function validateRequestWindow(CarbonImmutable $requestedStartsAt, CarbonImmutable $requestedEndsAt): void
    {
        if ($requestedEndsAt->lessThanOrEqualTo($requestedStartsAt)) {
            throw ValidationException::withMessages([
                'requested_ends_at' => __('capell-bookings::validation.appointment_end_after_start'),
            ]);
        }
    }

    private function validateAvailability(AppointmentRequestData $appointmentRequestData, CarbonImmutable $requestedEndsAt): void
    {
        $localStartsAt = $appointmentRequestData->requestedStartsAt->setTimezone($appointmentRequestData->timezone);
        $localEndsAt = $requestedEndsAt->setTimezone($appointmentRequestData->timezone);
        $localDate = $localStartsAt->toDateString();

        $available = BookingAvailabilityWindow::query()
            ->available()
            ->where('day_of_week', $localStartsAt->dayOfWeek)
            ->where('starts_at', '<=', $localStartsAt->toTimeString())
            ->where('ends_at', '>=', $localEndsAt->toTimeString())
            ->where(function (Builder $query) use ($localDate): void {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $localDate);
            })
            ->where(function (Builder $query) use ($localDate): void {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $localDate);
            })
            ->where(function (Builder $query) use ($appointmentRequestData): void {
                $query->whereNull('service_id')
                    ->orWhere('service_id', $appointmentRequestData->serviceId);
            })
            ->where(function (Builder $query) use ($appointmentRequestData): void {
                $query->whereNull('staff_member_id');

                if ($appointmentRequestData->staffMemberId !== null) {
                    $query->orWhere('staff_member_id', $appointmentRequestData->staffMemberId);
                }
            })
            ->where(function (Builder $query) use ($appointmentRequestData): void {
                $query->whereNull('location_id');

                if ($appointmentRequestData->locationId !== null) {
                    $query->orWhere('location_id', $appointmentRequestData->locationId);
                }
            })
            ->exists();

        if (! $available) {
            throw ValidationException::withMessages([
                'requested_starts_at' => __('capell-bookings::validation.appointment_outside_availability'),
            ]);
        }
    }
}
