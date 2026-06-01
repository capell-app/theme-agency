<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingLocations\Pages;

use Capell\Bookings\Filament\Resources\BookingLocations\BookingLocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListBookingLocations extends ListRecords
{
    protected static string $resource = BookingLocationResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
