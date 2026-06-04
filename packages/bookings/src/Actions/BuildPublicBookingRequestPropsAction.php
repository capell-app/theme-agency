<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use DateTimeZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{options: array<string, mixed>, slots: mixed, postUrl: string, timezone: string, timezoneOptions: list<string>} run(Request $request, bool $lazySlots = true)
 */
class BuildPublicBookingRequestPropsAction
{
    use AsAction;

    /**
     * @return array{options: array<string, mixed>, slots: mixed, postUrl: string, timezone: string, timezoneOptions: list<string>}
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
}
