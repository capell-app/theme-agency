<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWaitlistEntries\Pages;

use Capell\Bookings\Filament\Resources\BookingWaitlistEntries\BookingWaitlistEntryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListBookingWaitlistEntries extends ListRecords
{
    protected static string $resource = BookingWaitlistEntryResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
