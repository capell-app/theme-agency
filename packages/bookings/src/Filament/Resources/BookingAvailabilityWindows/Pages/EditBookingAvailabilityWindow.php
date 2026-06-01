<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\Pages;

use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\BookingAvailabilityWindowResource;
use Filament\Resources\Pages\EditRecord;

final class EditBookingAvailabilityWindow extends EditRecord
{
    protected static string $resource = BookingAvailabilityWindowResource::class;
}
