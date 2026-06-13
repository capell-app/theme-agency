<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingGroupSessions\Pages;

use Capell\Bookings\Filament\Resources\BookingGroupSessions\BookingGroupSessionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListBookingGroupSessions extends ListRecords
{
    protected static string $resource = BookingGroupSessionResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
