<?php

declare(strict_types=1);

namespace Capell\Bookings\Filament\Resources\AppointmentRequests\Pages;

use Capell\Bookings\Filament\Resources\AppointmentRequests\AppointmentRequestResource;
use Filament\Resources\Pages\EditRecord;
use Override;

final class EditAppointmentRequest extends EditRecord
{
    protected static string $resource = AppointmentRequestResource::class;

    #[Override]
    protected function getHeaderActions(): array
    {
        return AppointmentRequestResource::appointmentWorkflowActions();
    }
}
