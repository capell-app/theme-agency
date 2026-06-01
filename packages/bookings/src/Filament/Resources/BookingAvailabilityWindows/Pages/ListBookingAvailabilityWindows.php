<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\Pages;

use Capell\Bookings\Filament\Resources\BookingAvailabilityWindows\BookingAvailabilityWindowResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListBookingAvailabilityWindows extends ListRecords
{
    protected static string $resource = BookingAvailabilityWindowResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
