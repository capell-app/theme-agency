<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Data\AppointmentRequestData;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class StoreBookingRequestController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'service_id' => ['required', 'integer', 'min:1'],
            'staff_member_id' => ['nullable', 'integer', 'min:1'],
            'location_id' => ['nullable', 'integer', 'min:1'],
            'requested_starts_at' => ['required', 'date'],
            'timezone' => ['required', 'string', 'max:64'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        CreateAppointmentRequestAction::run(new AppointmentRequestData(
            serviceId: (int) $validated['service_id'],
            requestedStartsAt: CarbonImmutable::parse((string) $validated['requested_starts_at'], (string) $validated['timezone']),
            timezone: (string) $validated['timezone'],
            customerName: (string) $validated['customer_name'],
            customerEmail: (string) $validated['customer_email'],
            staffMemberId: $this->optionalInt($validated['staff_member_id'] ?? null),
            locationId: $this->optionalInt($validated['location_id'] ?? null),
            customerPhone: $this->optionalString($validated['customer_phone'] ?? null),
            notes: $this->optionalString($validated['notes'] ?? null),
            source: 'bookings-public',
            payload: [
                'submitted_from' => 'public-booking-request',
            ],
        ));

        return redirect()
            ->route('capell-bookings.request')
            ->with('booking_request_status', __('capell-bookings::generic.frontend.request_submitted'));
    }

    private function optionalInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function optionalString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
