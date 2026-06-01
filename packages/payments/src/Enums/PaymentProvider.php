<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentProvider: string implements HasLabel
{
    case Stripe = 'stripe';

    public function getLabel(): string
    {
        return __('capell-payments::generic.providers.' . $this->value);
    }
}
