<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianSlotBookingStatusEnum: string implements HasLabel
{
    case Held = 'held';
    case Confirmed = 'confirmed';
    case Cancelled = 'cancelled';
    case Expired = 'expired';
    case NoShow = 'no_show';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.slot_booking_statuses.' . $this->value);
    }
}
