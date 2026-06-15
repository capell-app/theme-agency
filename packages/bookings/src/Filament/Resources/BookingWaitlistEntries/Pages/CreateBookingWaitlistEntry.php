<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWaitlistEntries\Pages;

use Capell\Bookings\Filament\Resources\BookingWaitlistEntries\BookingWaitlistEntryResource;
use Filament\Resources\Pages\CreateRecord;

final class CreateBookingWaitlistEntry extends CreateRecord
{
    protected static string $resource = BookingWaitlistEntryResource::class;
}
