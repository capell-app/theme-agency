<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingChangeProposalStatusEnum: string implements HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Expired = 'expired';

    public function isOpen(): bool
    {
        return $this === self::Pending;
    }

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_change_proposal_status_' . $this->value);
    }
}
