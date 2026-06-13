<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingMessageLogs\Pages;

use Capell\Bookings\Filament\Resources\BookingMessageLogs\BookingMessageLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListBookingMessageLogs extends ListRecords
{
    protected static string $resource = BookingMessageLogResource::class;
}
