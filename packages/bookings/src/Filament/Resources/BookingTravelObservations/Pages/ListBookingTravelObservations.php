<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingTravelObservations\Pages;

use Capell\Bookings\Filament\Resources\BookingTravelObservations\BookingTravelObservationResource;
use Filament\Resources\Pages\ListRecords;

final class ListBookingTravelObservations extends ListRecords
{
    protected static string $resource = BookingTravelObservationResource::class;
}
