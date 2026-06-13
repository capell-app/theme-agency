<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingGroupSessions\Pages;

use Capell\Bookings\Filament\Resources\BookingGroupSessions\BookingGroupSessionResource;
use Filament\Resources\Pages\EditRecord;

final class EditBookingGroupSession extends EditRecord
{
    protected static string $resource = BookingGroupSessionResource::class;
}
