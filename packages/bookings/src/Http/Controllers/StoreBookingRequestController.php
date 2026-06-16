<?php

declare(strict_types=1);

namespace Capell\Bookings\Http\Controllers;

use Capell\Bookings\Actions\CreateAppointmentRequestAction;
use Capell\Bookings\Data\AppointmentRequestData;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use DateTimeZone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

final class StoreBookingRequestController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = Validator::make($request->all(), [
            'service_id' => ['required', 'integer', 'min:1'],
            'staff_member_id' => ['nullable', 'integer', 'min:1'],
            'location_id' => ['nullable', 'integer', 'min:1'],
            'requested_starts_at' => ['required', 'date'],
            'timezone' => ['required', 'string', 'max:64', Rule::in(DateTimeZone::listIdentifiers())],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ])->validate();

        $appointmentRequest = CreateAppointmentRequestAction::run(new AppointmentRequestData(
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
        $message = __('capell-bookings::generic.frontend.request_submitted');

        return to_route('capell-bookings.request')
            ->with('booking_request_status', $message)
            ->with('booking_request_success', [
                'submitted' => true,
                'status' => 'received',
                'message' => $message,
                'reference' => $this->publicReference($appointmentRequest),
            ]);
    }

    private function optionalInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function optionalString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function publicReference(AppointmentRequest $appointmentRequest): string
    {
        return Str::upper(Str::substr(hash('xxh128', (string) $appointmentRequest->calendar_uid), 0, 10));
    }
}
