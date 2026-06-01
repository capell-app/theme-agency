<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Models\BookingLocation;
use Capell\Bookings\Models\BookingService;
use Capell\Bookings\Models\BookingStaffMember;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static array{services: list<array<string, mixed>>, staff: list<array<string, mixed>>, locations: list<array<string, mixed>>} run()
 */
final class BuildPublicBookingRequestOptionsAction
{
    use AsAction;

    /**
     * @return array{services: list<array<string, mixed>>, staff: list<array<string, mixed>>, locations: list<array<string, mixed>>}
     */
    public function handle(): array
    {
        return [
            'services' => BookingService::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'description', 'duration_minutes'])
                ->map(static fn (BookingService $service): array => [
                    'id' => (int) $service->getKey(),
                    'name' => $service->name,
                    'description' => $service->description,
                    'duration_minutes' => $service->duration_minutes,
                ])
                ->all(),
            'staff' => BookingStaffMember::query()
                ->active()
                ->orderBy('display_name')
                ->get(['id', 'display_name', 'title'])
                ->map(static fn (BookingStaffMember $staffMember): array => [
                    'id' => (int) $staffMember->getKey(),
                    'display_name' => $staffMember->display_name,
                    'title' => $staffMember->title,
                ])
                ->all(),
            'locations' => BookingLocation::query()
                ->active()
                ->orderBy('name')
                ->get(['id', 'name', 'type', 'city'])
                ->map(static fn (BookingLocation $location): array => [
                    'id' => (int) $location->getKey(),
                    'name' => $location->name,
                    'type' => $location->type->getLabel(),
                    'city' => $location->city,
                ])
                ->all(),
        ];
    }
}
