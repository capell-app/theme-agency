<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

enum AppointmentNotificationTypeEnum: string
{
    case Confirmation = 'confirmation';
    case Cancellation = 'cancellation';
    case Reminder = 'reminder';

    public function subject(): string
    {
        return __('capell-bookings::notification.' . $this->value . '.subject');
    }

    public function greeting(): string
    {
        return __('capell-bookings::notification.' . $this->value . '.greeting');
    }

    public function body(): string
    {
        return __('capell-bookings::notification.' . $this->value . '.body');
    }
}
