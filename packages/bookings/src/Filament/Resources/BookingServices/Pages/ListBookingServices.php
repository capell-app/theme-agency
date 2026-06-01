<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingServices\Pages;

use Capell\Bookings\Filament\Resources\BookingServices\BookingServiceResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

final class ListBookingServices extends ListRecords
{
    protected static string $resource = BookingServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
