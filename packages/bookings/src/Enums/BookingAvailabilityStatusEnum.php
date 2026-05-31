<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingAvailabilityStatusEnum: string implements HasLabel
{
    case Available = 'available';
    case Blocked = 'blocked';

    public function allowsRequests(): bool
    {
        return $this === self::Available;
    }

    public function getLabel(): string
    {
        return __('capell-bookings::enum.availability_status_' . $this->value);
    }
}
