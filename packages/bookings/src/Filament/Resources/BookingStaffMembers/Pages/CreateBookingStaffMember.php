<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingStaffMembers\Pages;

use Capell\Bookings\Filament\Resources\BookingStaffMembers\BookingStaffMemberResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBookingStaffMember extends CreateRecord
{
    protected static string $resource = BookingStaffMemberResource::class;
}
