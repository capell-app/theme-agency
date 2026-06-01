<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingLocations\Pages;

use Capell\Bookings\Filament\Resources\BookingLocations\BookingLocationResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBookingLocation extends CreateRecord
{
    protected static string $resource = BookingLocationResource::class;
}
