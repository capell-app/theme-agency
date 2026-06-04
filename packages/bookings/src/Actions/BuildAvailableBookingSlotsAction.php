<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentRequest;
use Capell\Bookings\Models\BookingAvailabilityException;
use Capell\Bookings\Models\BookingAvailabilityWindow;
use Capell\Bookings\Models\BookingService;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static list<array<string, mixed>> run(?int $serviceId = null, ?int $staffMemberId = null, ?int $locationId = null, ?string $timezone = null, ?CarbonImmutable $from = null, int $days = 14)
 */
class BuildAvailableBookingSlotsAction
{
    use AsAction;

    /**
     * @return list<array<string, mixed>>
     */
    public function handle(
        ?int $serviceId = null,
        ?int $staffMemberId = null,
        ?int $locationId = null,
        ?string $timezone = null,
        ?CarbonImmutable $from = null,
        int $days = 14,
    ): array {
        if ($serviceId === null || $serviceId < 1) {
            return [];
        }

        /** @var BookingService|null $service */
        $service = BookingService::query()->active()->find($serviceId);

        if (! $service instanceof BookingService) {
            return [];
        }

        $timezone = $this->safeTimezone($timezone);
        $startsFrom = ($from ?? CarbonImmutable::now($timezone))->setTimezone($timezone)->startOfDay();
        $days = max(1, min($days, $service->max_future_days ?? $days));
        $slots = [];

        foreach (range(0, $days - 1) as $dayOffset) {
            $date = $startsFrom->addDays($dayOffset);
            $slots = [
                ...$slots,
                ...$this->slotsForDate($service, $date, $timezone, $staffMemberId, $locationId),
            ];
        }

        return array_values($slots);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function slotsForDate(
        BookingService $service,
        CarbonImmutable $date,
        string $timezone,
        ?int $staffMemberId,
        ?int $locationId,
    ): array {
        /** @var EloquentCollection<int, BookingAvailabilityWindow> $windows */
        $windows = BookingAvailabilityWindow::query()
            ->available()
            ->where('day_of_week', $date->dayOfWeek)
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $date->toDateString());
            })
            ->where(function (Builder $query) use ($date): void {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $date->toDateString());
            })
            ->where(function (Builder $query) use ($service): void {
                $query->whereNull('service_id')
                    ->orWhere('service_id', $service->getKey());
            })
            ->where(function (Builder $query) use ($staffMemberId): void {
                $query->whereNull('staff_member_id');

                if ($staffMemberId !== null) {
                    $query->orWhere('staff_member_id', $staffMemberId);
                }
            })
            ->where(function (Builder $query) use ($locationId): void {
                $query->whereNull('location_id');

                if ($locationId !== null) {
                    $query->orWhere('location_id', $locationId);
                }
            })
            ->get();

        $slots = [];
        $stepMinutes = max(5, (int) config('capell-bookings.public_slot_interval_minutes', 15));

        foreach ($windows as $window) {
            $slots = $this->mergeSlotsFromRange(
                slots: $slots,
                service: $service,
                startsAt: CarbonImmutable::parse($date->toDateString() . ' ' . $window->starts_at, $timezone),
                endsAt: CarbonImmutable::parse($date->toDateString() . ' ' . $window->ends_at, $timezone),
                timezone: $timezone,
                staffMemberId: $staffMemberId,
                locationId: $locationId,
                stepMinutes: $stepMinutes,
            );
        }

        /** @var EloquentCollection<int, BookingAvailabilityException> $availableExceptions */
        $availableExceptions = BookingAvailabilityException::query()
            ->available()
            ->whereDate('date', $date->toDateString())
            ->whereNotNull('starts_at')
            ->whereNotNull('ends_at')
            ->whereNotNull('capacity')
            ->where(function (Builder $query) use ($service): void {
                $query->whereNull('service_id')
                    ->orWhere('service_id', $service->getKey());
            })
            ->where(function (Builder $query) use ($staffMemberId): void {
                $query->whereNull('staff_member_id');

                if ($staffMemberId !== null) {
                    $query->orWhere('staff_member_id', $staffMemberId);
                }
            })
            ->where(function (Builder $query) use ($locationId): void {
                $query->whereNull('location_id');

                if ($locationId !== null) {
                    $query->orWhere('location_id', $locationId);
                }
            })
            ->get();

        foreach ($availableExceptions as $availableException) {
            if ($availableException->starts_at === null || $availableException->ends_at === null) {
                continue;
            }

            $slots = $this->mergeSlotsFromRange(
                slots: $slots,
                service: $service,
                startsAt: CarbonImmutable::parse($date->toDateString() . ' ' . $availableException->starts_at, $timezone),
                endsAt: CarbonImmutable::parse($date->toDateString() . ' ' . $availableException->ends_at, $timezone),
                timezone: $timezone,
                staffMemberId: $staffMemberId,
                locationId: $locationId,
                stepMinutes: $stepMinutes,
            );
        }

        ksort($slots);

        return array_values($slots);
    }

    /**
     * @param  array<string, array<string, mixed>>  $slots
     * @return array<string, array<string, mixed>>
     */
    private function mergeSlotsFromRange(
        array $slots,
        BookingService $service,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        string $timezone,
        ?int $staffMemberId,
        ?int $locationId,
        int $stepMinutes,
    ): array {
        $slotStart = $startsAt;

        while ($slotStart->addMinutes($service->duration_minutes)->lessThanOrEqualTo($endsAt)) {
            $slotEnd = $slotStart->addMinutes($service->duration_minutes);
            $slot = $this->slot($service, $slotStart, $slotEnd, $timezone, $staffMemberId, $locationId);

            if ($slot !== null) {
                $slots[$slot['starts_at']] = $slot;
            }

            $slotStart = $slotStart->addMinutes($stepMinutes);
        }

        return $slots;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function slot(
        BookingService $service,
        CarbonImmutable $startsAt,
        CarbonImmutable $endsAt,
        string $timezone,
        ?int $staffMemberId,
        ?int $locationId,
    ): ?array {
        $appointmentRequestData = new AppointmentRequestData(
            serviceId: (int) $service->getKey(),
            requestedStartsAt: $startsAt,
            timezone: $timezone,
            customerName: 'Preview',
            customerEmail: 'preview@example.test',
            requestedEndsAt: $endsAt,
            staffMemberId: $staffMemberId,
            locationId: $locationId,
        );

        if (! $this->insideRequestWindow($service, $startsAt)) {
            return null;
        }

        $capacity = $this->availabilityCapacity($appointmentRequestData, $endsAt);

        if ($capacity === null) {
            return null;
        }

        $remaining = $capacity - $this->blockingAppointmentCount($service, $appointmentRequestData, $endsAt);

        if ($remaining < 1) {
            return null;
        }

        return [
            'starts_at' => $startsAt->toIso8601String(),
            'ends_at' => $endsAt->toIso8601String(),
            'label' => $startsAt->isoFormat('ddd D MMM, HH:mm'),
            'timezone' => $timezone,
            'capacity_remaining' => $remaining,
        ];
    }

    private function insideRequestWindow(BookingService $service, CarbonImmutable $startsAt): bool
    {
        $now = CarbonImmutable::now($startsAt->getTimezone());

        if ($startsAt->lessThan($now->addMinutes($service->lead_time_minutes))) {
            return false;
        }

        return $service->max_future_days === null
            || $startsAt->lessThanOrEqualTo($now->addDays($service->max_future_days));
    }

    private function availabilityCapacity(AppointmentRequestData $appointmentRequestData, CarbonImmutable $endsAt): ?int
    {
        $exception = $this->matchingAvailabilityException($appointmentRequestData, $endsAt);

        if ($exception instanceof BookingAvailabilityException) {
            return $exception->status->allowsRequests() && $exception->capacity !== null
                ? $exception->capacity
                : null;
        }

        $window = $this->matchingAvailabilityWindow($appointmentRequestData, $endsAt);

        return $window?->capacity;
    }

    private function matchingAvailabilityException(
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $endsAt,
    ): ?BookingAvailabilityException {
        $startsAt = $appointmentRequestData->requestedStartsAt->setTimezone($appointmentRequestData->timezone);
        $endsAt = $endsAt->setTimezone($appointmentRequestData->timezone);

        /** @var EloquentCollection<int, BookingAvailabilityException> $exceptions */
        $exceptions = BookingAvailabilityException::query()
            ->whereDate('date', $startsAt->toDateString())
            ->where(function (Builder $query) use ($startsAt): void {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', $startsAt->toTimeString());
            })
            ->where(function (Builder $query) use ($endsAt): void {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', $endsAt->toTimeString());
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

        return $exceptions
            ->sortByDesc(fn (BookingAvailabilityException $exception): int => $exception->capacity ?? 0)
            ->sortByDesc(fn (BookingAvailabilityException $exception): int => $this->exceptionSpecificity($exception))
            ->first();
    }

    private function matchingAvailabilityWindow(
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $endsAt,
    ): ?BookingAvailabilityWindow {
        $startsAt = $appointmentRequestData->requestedStartsAt->setTimezone($appointmentRequestData->timezone);
        $endsAt = $endsAt->setTimezone($appointmentRequestData->timezone);

        /** @var EloquentCollection<int, BookingAvailabilityWindow> $windows */
        $windows = BookingAvailabilityWindow::query()
            ->available()
            ->where('day_of_week', $startsAt->dayOfWeek)
            ->where('starts_at', '<=', $startsAt->toTimeString())
            ->where('ends_at', '>=', $endsAt->toTimeString())
            ->where(function (Builder $query) use ($startsAt): void {
                $query->whereNull('effective_from')
                    ->orWhere('effective_from', '<=', $startsAt->toDateString());
            })
            ->where(function (Builder $query) use ($startsAt): void {
                $query->whereNull('effective_until')
                    ->orWhere('effective_until', '>=', $startsAt->toDateString());
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

        return $windows
            ->sortByDesc(fn (BookingAvailabilityWindow $window): int => $window->capacity)
            ->sortByDesc(fn (BookingAvailabilityWindow $window): int => $this->windowSpecificity($window))
            ->first();
    }

    private function blockingAppointmentCount(
        BookingService $service,
        AppointmentRequestData $appointmentRequestData,
        CarbonImmutable $endsAt,
    ): int {
        $blockingStatuses = array_map(
            static fn (AppointmentRequestStatusEnum $status): string => $status->value,
            array_filter(
                AppointmentRequestStatusEnum::cases(),
                static fn (AppointmentRequestStatusEnum $status): bool => $status->blocksCapacity(),
            ),
        );
        $blockedStartsAt = $appointmentRequestData->requestedStartsAt->subMinutes($service->buffer_before_minutes);
        $blockedEndsAt = $endsAt->addMinutes($service->buffer_after_minutes);
        $existingStartsBefore = $blockedEndsAt->addMinutes($service->buffer_before_minutes);
        $existingEndsAfter = $blockedStartsAt->subMinutes($service->buffer_after_minutes);

        return AppointmentRequest::query()
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
    }

    private function windowSpecificity(BookingAvailabilityWindow $window): int
    {
        return (int) ($window->service_id !== null)
            + (int) ($window->staff_member_id !== null)
            + (int) ($window->location_id !== null);
    }

    private function exceptionSpecificity(BookingAvailabilityException $exception): int
    {
        return (int) ($exception->service_id !== null)
            + (int) ($exception->staff_member_id !== null)
            + (int) ($exception->location_id !== null)
            + (int) ($exception->starts_at !== null)
            + (int) ($exception->ends_at !== null);
    }

    private function safeTimezone(?string $timezone): string
    {
        if (is_string($timezone) && in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            return $timezone;
        }

        return (string) config('app.timezone', 'UTC');
    }
}
