<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWorkZones\Pages;

use Capell\Bookings\Filament\Resources\BookingWorkZones\BookingWorkZoneResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListBookingWorkZones extends ListRecords
{
    protected static string $resource = BookingWorkZoneResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
