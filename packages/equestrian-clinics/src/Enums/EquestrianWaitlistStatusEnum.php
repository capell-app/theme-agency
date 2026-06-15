<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianWaitlistStatusEnum: string implements HasLabel
{
    case Waiting = 'waiting';
    case Offered = 'offered';
    case Claimed = 'claimed';
    case Expired = 'expired';
    case Cancelled = 'cancelled';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.waitlist_statuses.' . $this->value);
    }
}
