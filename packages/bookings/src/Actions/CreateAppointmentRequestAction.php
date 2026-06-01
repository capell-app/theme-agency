<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
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

            $this->validateRequestWindow($service, $appointmentRequestData->requestedStartsAt, $requestedEndsAt);
            $availabilityCapacity = $this->resolveAvailabilityCapacity($appointmentRequestData, $requestedEndsAt);
            $this->validateCapacity($service, $availabilityCapacity, $appointmentRequestData, $requestedEndsAt);

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

            RecordAppointmentAuditLogAction::run(
                appointmentRequest: $appointmentRequest,
                event: AppointmentAuditEventEnum::Created,
                statusTo: AppointmentRequestStatusEnum::Requested,
            );

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

    private function validateRequestWindow(
        BookingService $service,
        CarbonImmutable $requestedStartsAt,
        CarbonImmutable $requestedEndsAt,
    ): void {
        if ($requestedEndsAt->lessThanOrEqualTo($requestedStartsAt)) {
            throw ValidationException::withMessages([
                'requested_ends_at' => __('capell-bookings::validation.appointment_end_after_start'),
            ]);
        }

        $minimumStartsAt = CarbonImmutable::now($requestedStartsAt->getTimezone())->addMinutes($service->lead_time_minutes);

        if ($requestedStartsAt->lessThan($minimumStartsAt)) {
            throw ValidationException::withMessages([
                'requested_starts_at' => __('capell-bookings::validation.appointment_before_lead_time'),
            ]);
        }

        if ($service->max_future_days === null) {
            return;
        }

        $maximumStartsAt = CarbonImmutable::now($requestedStartsAt->getTimezone())->addDays($service->max_future_days);

        if ($requestedStartsAt->greaterThan($maximumStartsAt)) {
            throw ValidationException::withMessages([
                'requested_starts_at' => __('capell-bookings::validation.appointment_after_max_future_days'),
            ]);
        }
    }

    private function resolveAvailabilityCapacity(
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $requestedEndsAt,
    ): int {
        $availabilityException = $this->matchingAvailabilityException($appointmentRequestData, $requestedEndsAt);

        if ($availabilityException !== null) {
            if ($availabilityException->status->allowsRequests() && $availabilityException->capacity !== null) {
                return $availabilityException->capacity;
            }

            throw ValidationException::withMessages([
                'requested_starts_at' => __('capell-bookings::validation.appointment_blocked_by_availability_exception'),
            ]);
        }

        return $this->matchingAvailabilityWindow($appointmentRequestData, $requestedEndsAt)->capacity;
    }

    private function matchingAvailabilityException(
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $requestedEndsAt,
    ): ?BookingAvailabilityException {
        $localStartsAt = $appointmentRequestData->requestedStartsAt->setTimezone($appointmentRequestData->timezone);
        $localEndsAt = $requestedEndsAt->setTimezone($appointmentRequestData->timezone);
        $localDate = $localStartsAt->toDateString();

        /** @var EloquentCollection<int, BookingAvailabilityException> $availabilityExceptions */
        $availabilityExceptions = BookingAvailabilityException::query()
            ->whereDate('date', $localDate)
            ->where(function (Builder $query) use ($localStartsAt): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $localStartsAt->toTimeString());
            })
            ->where(function (Builder $query) use ($localEndsAt): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $localEndsAt->toTimeString());
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
            ->get();

        if ($availabilityExceptions->isEmpty()) {
            return null;
        }

        return $availabilityExceptions
            ->sortByDesc(fn (BookingAvailabilityException $availabilityException): int => $availabilityException->capacity ?? 0)
            ->sortByDesc(fn (BookingAvailabilityException $availabilityException): int => $this->availabilityExceptionSpecificity($availabilityException))
            ->first();
    }

    private function matchingAvailabilityWindow(
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $requestedEndsAt,
    ): BookingAvailabilityWindow {
        $localStartsAt = $appointmentRequestData->requestedStartsAt->setTimezone($appointmentRequestData->timezone);
        $localEndsAt = $requestedEndsAt->setTimezone($appointmentRequestData->timezone);
        $localDate = $localStartsAt->toDateString();

        /** @var EloquentCollection<int, BookingAvailabilityWindow> $availabilityWindows */
        $availabilityWindows = BookingAvailabilityWindow::query()
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
            ->get();

        if ($availabilityWindows->isEmpty()) {
            throw ValidationException::withMessages([
                'requested_starts_at' => __('capell-bookings::validation.appointment_outside_availability'),
            ]);
        }

        return $availabilityWindows
            ->sortByDesc(fn (BookingAvailabilityWindow $availabilityWindow): int => $availabilityWindow->capacity)
            ->sortByDesc(fn (BookingAvailabilityWindow $availabilityWindow): int => $this->availabilitySpecificity($availabilityWindow))
            ->firstOrFail();
    }

    private function availabilitySpecificity(BookingAvailabilityWindow $availabilityWindow): int
    {
        return (int) ($availabilityWindow->service_id !== null)
            + (int) ($availabilityWindow->staff_member_id !== null)
            + (int) ($availabilityWindow->location_id !== null);
    }

    private function availabilityExceptionSpecificity(BookingAvailabilityException $availabilityException): int
    {
        return (int) ($availabilityException->service_id !== null)
            + (int) ($availabilityException->staff_member_id !== null)
            + (int) ($availabilityException->location_id !== null)
            + (int) ($availabilityException->starts_at !== null)
            + (int) ($availabilityException->ends_at !== null);
    }

    private function validateCapacity(
        BookingService $service,
        int $availabilityCapacity,
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $requestedEndsAt,
    ): void {
        $blockingStatuses = array_map(
            static fn (AppointmentRequestStatusEnum $status): string => $status->value,
            array_filter(
                AppointmentRequestStatusEnum::cases(),
                static fn (AppointmentRequestStatusEnum $status): bool => $status->blocksCapacity(),
            ),
        );

        $blockedStartsAt = $appointmentRequestData->requestedStartsAt->subMinutes($service->buffer_before_minutes);
        $blockedEndsAt = $requestedEndsAt->addMinutes($service->buffer_after_minutes);
        $existingStartsBefore = $blockedEndsAt->addMinutes($service->buffer_before_minutes);
        $existingEndsAfter = $blockedStartsAt->subMinutes($service->buffer_after_minutes);

        $blockingAppointmentCount = AppointmentRequest::query()
            ->where('service_id', $service->getKey())
            ->whereIn('status', $blockingStatuses)
            ->where('requested_starts_at', '<', $existingStartsBefore)
            ->where('requested_ends_at', '>', $existingEndsAfter)
            ->where(function (Builder $query) use ($appointmentRequestData): void {
                if ($appointmentRequestData->staffMemberId === null) {
                    return;
                }

                $query->whereNull('staff_member_id')
                    ->orWhere('staff_member_id', $appointmentRequestData->staffMemberId);
            })
            ->where(function (Builder $query) use ($appointmentRequestData): void {
                if ($appointmentRequestData->locationId === null) {
                    return;
                }

                $query->whereNull('location_id')
                    ->orWhere('location_id', $appointmentRequestData->locationId);
            })
            ->count();

        if ($blockingAppointmentCount < $availabilityCapacity) {
            return;
        }

        throw ValidationException::withMessages([
            'requested_starts_at' => __('capell-bookings::validation.appointment_capacity_exceeded'),
        ]);
    }
}
