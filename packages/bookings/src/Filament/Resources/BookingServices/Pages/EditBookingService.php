<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingServices\Pages;

use Capell\Bookings\Filament\Resources\BookingServices\BookingServiceResource;
use Filament\Resources\Pages\EditRecord;

final class EditBookingService extends EditRecord
{
    protected static string $resource = BookingServiceResource::class;
}
