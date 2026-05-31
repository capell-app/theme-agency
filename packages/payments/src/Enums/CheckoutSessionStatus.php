<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum CheckoutSessionStatus: string implements HasLabel
{
    case Open = 'open';
    case Complete = 'complete';
    case Expired = 'expired';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return __('capell-payments::generic.checkout_statuses.' . $this->value);
    }
}
