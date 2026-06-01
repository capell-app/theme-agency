<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\Pages;

use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\BookingAvailabilityExceptionResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBookingAvailabilityException extends CreateRecord
{
    protected static string $resource = BookingAvailabilityExceptionResource::class;
}
