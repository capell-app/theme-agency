<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum AppointmentRequestStatusEnum: string implements HasLabel
{
    case Provisional = 'provisional';
    case Requested = 'requested';
    case Confirmed = 'confirmed';
    case Declined = 'declined';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case NoShow = 'no_show';

    public function canConfirm(): bool
    {
        return in_array($this, [self::Provisional, self::Requested], true);
    }

    public function canCancel(): bool
    {
        return in_array($this, [self::Provisional, self::Requested, self::Confirmed], true);
    }

    public function blocksCapacity(): bool
    {
        return in_array($this, [self::Provisional, self::Requested, self::Confirmed], true);
    }

    public function getLabel(): string
    {
        return __('capell-bookings::enum.appointment_request_status_' . $this->value);
    }
}
