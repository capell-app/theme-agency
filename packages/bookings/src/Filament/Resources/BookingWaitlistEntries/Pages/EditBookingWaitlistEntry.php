<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingWaitlistEntries\Pages;

use Capell\Bookings\Filament\Resources\BookingWaitlistEntries\BookingWaitlistEntryResource;
use Filament\Resources\Pages\EditRecord;

final class EditBookingWaitlistEntry extends EditRecord
{
    protected static string $resource = BookingWaitlistEntryResource::class;
}
