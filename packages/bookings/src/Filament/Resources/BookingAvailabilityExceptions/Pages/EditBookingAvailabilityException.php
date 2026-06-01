<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\Pages;

use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\BookingAvailabilityExceptionResource;
use Filament\Resources\Pages\EditRecord;

final class EditBookingAvailabilityException extends EditRecord
{
    protected static string $resource = BookingAvailabilityExceptionResource::class;
}
