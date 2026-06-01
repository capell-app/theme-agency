<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingStaffMembers\Pages;

use Capell\Bookings\Filament\Resources\BookingStaffMembers\BookingStaffMemberResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Override;

final class ListBookingStaffMembers extends ListRecords
{
    protected static string $resource = BookingStaffMemberResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
