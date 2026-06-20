<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{options: array{services: list<array<string, mixed>>, staff: list<array<string, mixed>>, locations: list<array<string, mixed>>}, slots: mixed, postUrl: string, success: array{submitted: bool, status: ?string, message: ?string, reference: ?string}, timezone: string, timezoneOptions: list<string>} run(Request $request, bool $lazySlots = true)
 */
class BuildPublicBookingRequestPropsAction
{
    use AsAction;

    /**
     * @return array{options: array{services: list<array<string, mixed>>, staff: list<array<string, mixed>>, locations: list<array<string, mixed>>}, slots: mixed, postUrl: string, success: array{submitted: bool, status: ?string, message: ?string, reference: ?string}, timezone: string, timezoneOptions: list<string>}
     */
    public function handle(Request $request, bool $lazySlots = true): array
    {
        $timezone = $this->safeTimezone($this->optionalString($request->query('timezone')));
        $slots = fn (): array => BuildAvailableBookingSlotsAction::run(
            serviceId: $this->optionalInt($request->query('service_id')),
            staffMemberId: $this->optionalInt($request->query('staff_member_id')),
            locationId: $this->optionalInt($request->query('location_id')),
            timezone: $timezone,
        );

        return [
            'options' => BuildPublicBookingRequestOptionsAction::run(),
            'slots' => $lazySlots && class_exists(Inertia::class)
                ? Inertia::optional($slots)
                : $slots(),
            'postUrl' => Route::has('capell-bookings.request.store')
                ? route('capell-bookings.request.store')
                : '/' . trim((string) config('capell-bookings.public_path_prefix', 'bookings'), '/'),
            'success' => $this->successState($request),
            'timezone' => $timezone,
            'timezoneOptions' => DateTimeZone::listIdentifiers(),
        ];
    }

    private function optionalInt(mixed $value): ?int
    {
        return is_numeric($value) && (int) $value > 0 ? (int) $value : null;
    }

    private function optionalString(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }

    private function safeTimezone(?string $timezone): string
    {
        if (is_string($timezone) && in_array($timezone, DateTimeZone::listIdentifiers(), true)) {
            return $timezone;
        }

        return (string) config('app.timezone', 'UTC');
    }

    /**
     * @return array{submitted: bool, status: ?string, message: ?string, reference: ?string}
     */
    private function successState(Request $request): array
    {
        if (! $request->hasSession()) {
            return [
                'submitted' => false,
                'status' => null,
                'message' => null,
                'reference' => null,
            ];
        }

        $success = $request->session()->get('booking_request_success');

        if (is_array($success)) {
            return [
                'submitted' => (bool) ($success['submitted'] ?? false),
                'status' => $this->optionalString($success['status'] ?? null),
                'message' => $this->optionalString($success['message'] ?? null),
                'reference' => $this->optionalString($success['reference'] ?? null),
            ];
        }

        $legacyMessage = $this->optionalString($request->session()->get('booking_request_status'));

        return [
            'submitted' => $legacyMessage !== null,
            'status' => $legacyMessage === null ? null : 'received',
            'message' => $legacyMessage,
            'reference' => null,
        ];
    }
}
