<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingOwnerPromptStatusEnum: string implements HasLabel
{
    case Proposed = 'proposed';
    case Accepted = 'accepted';
    case Rejected = 'rejected';
    case Applied = 'applied';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_owner_prompt_status_' . $this->value);
    }
}
