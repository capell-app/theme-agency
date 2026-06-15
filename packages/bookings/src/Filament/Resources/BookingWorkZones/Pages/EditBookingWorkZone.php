<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWorkZones\Pages;

use Capell\Bookings\Filament\Resources\BookingWorkZones\BookingWorkZoneResource;
use Filament\Resources\Pages\EditRecord;

final class EditBookingWorkZone extends EditRecord
{
    protected static string $resource = BookingWorkZoneResource::class;
}
