<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingWorkZone;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static bool run(BookingLocation $location)
 */
class EnforceWorkZoneAction
{
    use AsAction;

    public function handle(BookingLocation $location): bool
    {
        if ($this->isCovered($location)) {
            return true;
        }

        throw ValidationException::withMessages([
            'location_id' => __('capell-bookings::validation.location_outside_work_zone'),
        ]);
    }

    private function isCovered(BookingLocation $location): bool
    {
        return BookingWorkZone::query()
            ->where('active', true)
            ->get()
            ->contains(function (BookingWorkZone $workZone) use ($location): bool {
                if ($workZone->service_area !== null && $workZone->service_area === $location->service_area) {
                    return true;
                }

                $normalizedPostalCode = Str::upper(str_replace(' ', '', (string) $location->postal_code));

                return collect($workZone->postal_code_prefixes ?? [])
                    ->contains(static fn (mixed $prefix): bool => $prefix !== null
                        && Str::startsWith($normalizedPostalCode, Str::upper(str_replace(' ', '', (string) $prefix))));
            });
    }
}
