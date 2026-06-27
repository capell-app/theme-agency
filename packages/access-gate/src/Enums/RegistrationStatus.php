<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum RegistrationStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Claimed = 'claimed';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.registration_status.%s', $this->value));
    }
}
