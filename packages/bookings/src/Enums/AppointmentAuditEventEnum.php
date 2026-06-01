<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

enum AppointmentAuditEventEnum: string
{
    case Created = 'created';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case ReminderQueued = 'reminder_queued';
    case NotificationQueued = 'notification_queued';
}
