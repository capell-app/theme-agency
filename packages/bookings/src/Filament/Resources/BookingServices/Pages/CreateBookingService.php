<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingServices\Pages;

use Capell\Bookings\Filament\Resources\BookingServices\BookingServiceResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBookingService extends CreateRecord
{
    protected static string $resource = BookingServiceResource::class;
}
