<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\AppointmentRequests\Pages;

use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
use Filament\Resources\Pages\EditRecord;

final class EditAppointmentRequest extends EditRecord
{
    protected static string $resource = AppointmentRequestResource::class;
}
