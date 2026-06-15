<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\BookingChangeProposals\Pages;

use Capell\Bookings\Filament\Resources\BookingChangeProposals\BookingChangeProposalResource;
use Filament\Resources\Pages\ListRecords;

final class ListBookingChangeProposals extends ListRecords
{
    protected static string $resource = BookingChangeProposalResource::class;
}
