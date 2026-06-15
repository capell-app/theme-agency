<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingMessageStatusEnum: string implements HasLabel
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Skipped = 'skipped';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_message_status_' . $this->value);
    }
}
