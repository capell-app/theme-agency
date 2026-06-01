<?php

declare(strict_types=1);

namespace Capell\Bookings\Models;

use Capell\Bookings\Enums\AppointmentAuditEventEnum;
use Capell\Bookings\Enums\AppointmentRequestStatusEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Override;

/**
 * @property AppointmentAuditEventEnum $event
 * @property CarbonImmutable $occurred_at
 * @property array<string, mixed>|null $payload
 * @property AppointmentRequestStatusEnum|null $status_from
 * @property AppointmentRequestStatusEnum|null $status_to
 */
class AppointmentAuditLog extends Model
{
    use HasFactory;

    protected $table = 'appointment_audit_logs';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'appointment_request_id',
        'event',
        'message',
        'occurred_at',
        'payload',
        'status_from',
        'status_to',
    ];

    /**
     * @return BelongsTo<AppointmentRequest, $this>
     */
    public function appointmentRequest(): BelongsTo
    {
        return $this->belongsTo(AppointmentRequest::class, 'appointment_request_id');
    }

    #[Override]
    protected function casts(): array
    {
        return [
            'event' => AppointmentAuditEventEnum::class,
            'occurred_at' => 'immutable_datetime',
            'payload' => 'json',
            'status_from' => AppointmentRequestStatusEnum::class,
            'status_to' => AppointmentRequestStatusEnum::class,
        ];
    }
}
