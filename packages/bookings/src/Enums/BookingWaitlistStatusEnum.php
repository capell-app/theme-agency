<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingWaitlistStatusEnum: string implements HasLabel
{
    case Waiting = 'waiting';
    case Offered = 'offered';
    case Booked = 'booked';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_waitlist_status.' . $this->value);
    }
}
