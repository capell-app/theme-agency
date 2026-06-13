<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingChangeProposalPartyStatusEnum: string implements HasLabel
{
    case Pending = 'pending';
    case Accepted = 'accepted';
    case Rejected = 'rejected';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_change_proposal_party_status_' . $this->value);
    }
}
