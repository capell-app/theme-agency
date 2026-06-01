<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Data\AvailabilityExceptionData;
use Capell\Bookings\Enums\BookingAvailabilityStatusEnum;
use Capell\Bookings\Models\BookingAvailabilityException;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static BookingAvailabilityException run(AvailabilityExceptionData $availabilityExceptionData)
 */
class CreateAvailabilityExceptionAction
{
    use AsAction;

    public function handle(AvailabilityExceptionData $availabilityExceptionData): BookingAvailabilityException
    {
        $this->validateTimes($availabilityExceptionData);
        $this->validateCapacity($availabilityExceptionData);

        /** @var BookingAvailabilityException $availabilityException */
        $availabilityException = BookingAvailabilityException::query()->create([
            'service_id' => $availabilityExceptionData->serviceId,
            'staff_member_id' => $availabilityExceptionData->staffMemberId,
            'location_id' => $availabilityExceptionData->locationId,
            'status' => $availabilityExceptionData->status,
            'date' => $availabilityExceptionData->date->toDateString(),
            'starts_at' => $availabilityExceptionData->startsAt,
            'ends_at' => $availabilityExceptionData->endsAt,
            'timezone' => $availabilityExceptionData->timezone,
            'capacity' => $availabilityExceptionData->capacity,
            'reason' => $availabilityExceptionData->reason,
            'notes' => $availabilityExceptionData->notes,
            'meta' => $availabilityExceptionData->meta,
        ]);

        return $availabilityException;
    }

    private function validateTimes(AvailabilityExceptionData $availabilityExceptionData): void
    {
        if ($availabilityExceptionData->startsAt === null && $availabilityExceptionData->endsAt === null) {
            return;
        }

        if ($availabilityExceptionData->startsAt !== null && $availabilityExceptionData->endsAt !== null && $availabilityExceptionData->endsAt > $availabilityExceptionData->startsAt) {
            return;
        }

        throw ValidationException::withMessages([
            'ends_at' => __('capell-bookings::validation.availability_exception_end_after_start'),
        ]);
    }

    private function validateCapacity(AvailabilityExceptionData $availabilityExceptionData): void
    {
        if ($availabilityExceptionData->status !== BookingAvailabilityStatusEnum::Available) {
            return;
        }

        if ($availabilityExceptionData->capacity !== null && $availabilityExceptionData->capacity > 0) {
            return;
        }

        throw ValidationException::withMessages([
            'capacity' => __('capell-bookings::validation.availability_exception_capacity_required'),
        ]);
    }
}
