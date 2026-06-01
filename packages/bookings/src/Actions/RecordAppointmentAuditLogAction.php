<?php

declare(strict_types=1);

namespace Capell\Bookings\Actions;

use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Capell\Bookings\Models\AppointmentAuditLog;
use Capell\Bookings\Models\AppointmentRequest;
use Carbon\CarbonImmutable;
use Lorisleiva\Actions\Concerns\AsAction;

/**
 * @method static AppointmentAuditLog run(AppointmentRequest $appointmentRequest, AppointmentAuditEventEnum $event, ?AppointmentRequestStatusEnum $statusFrom = null, ?AppointmentRequestStatusEnum $statusTo = null, ?string $message = null, array $payload = [])
 */
class RecordAppointmentAuditLogAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(
        AppointmentRequest $appointmentRequest,
        AppointmentAuditEventEnum $event,
        ?AppointmentRequestStatusEnum $statusFrom = null,
        ?AppointmentRequestStatusEnum $statusTo = null,
        ?string $message = null,
        array $payload = [],
    ): AppointmentAuditLog {
        /** @var AppointmentAuditLog $auditLog */
        $auditLog = AppointmentAuditLog::query()->create([
            'appointment_request_id' => $appointmentRequest->getKey(),
            'event' => $event,
            'status_from' => $statusFrom,
            'status_to' => $statusTo,
            'message' => $message,
            'payload' => $payload,
            'occurred_at' => CarbonImmutable::now(),
        ]);

        return $auditLog;
    }
}
