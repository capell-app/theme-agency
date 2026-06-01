<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\Pages;

use Capell\Bookings\Filament\Resources\BookingAvailabilityExceptions\BookingAvailabilityExceptionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListBookingAvailabilityExceptions extends ListRecords
{
    protected static string $resource = BookingAvailabilityExceptionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
