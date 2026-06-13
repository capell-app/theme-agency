<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingOwnerPrompts\Pages;

use Capell\Bookings\Filament\Resources\BookingOwnerPrompts\BookingOwnerPromptResource;
use Filament\Resources\Pages\ListRecords;

final class ListBookingOwnerPrompts extends ListRecords
{
    protected static string $resource = BookingOwnerPromptResource::class;
}
