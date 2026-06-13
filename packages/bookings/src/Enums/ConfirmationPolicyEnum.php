<?php

declare(strict_types=1);

namespace Capell\Bookings\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConfirmationPolicyEnum: string implements HasLabel
{
    case Manual = 'manual';
    case SelfConfirmLink = 'self_confirm_link';
    case Payment = 'payment';

    public function getLabel(): string
    {
        return __('capell-bookings::enum.confirmation_policy_' . $this->value);
    }
}
