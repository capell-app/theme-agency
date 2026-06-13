<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingGroupSessionStatusEnum: string implements HasLabel
{
    case Draft = 'draft';
    case Published = 'published';
    case Closed = 'closed';

    public function acceptsRegistrations(): bool
    {
        return in_array($this, [self::Draft, self::Published], true);
    }

    public function getLabel(): string
    {
        return __('capell-bookings::enum.booking_group_session_status_' . $this->value);
    }
}
