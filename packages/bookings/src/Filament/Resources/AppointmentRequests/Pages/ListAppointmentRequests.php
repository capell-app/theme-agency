<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\AppointmentRequests\Pages;

use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
use Filament\Resources\Pages\ListRecords;

final class ListAppointmentRequests extends ListRecords
{
    protected static string $resource = AppointmentRequestResource::class;
}
