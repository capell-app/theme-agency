<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum BookingLocationTypeEnum: string implements HasLabel
{
    case Physical = 'physical';
    case Virtual = 'virtual';
    case Phone = 'phone';
    case Onsite = 'onsite';
    case ToBeConfirmed = 'to_be_confirmed';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.location_type_' . $this->value);
    }
}
