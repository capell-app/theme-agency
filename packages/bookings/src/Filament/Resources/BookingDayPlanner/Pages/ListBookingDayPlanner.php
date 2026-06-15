<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingDayPlanner\Pages;

use Capell\Bookings\Filament\Resources\BookingDayPlanner\BookingDayPlannerResource;
use Filament\Resources\Pages\ListRecords;

final class ListBookingDayPlanner extends ListRecords
{
    protected static string $resource = BookingDayPlannerResource::class;
}
