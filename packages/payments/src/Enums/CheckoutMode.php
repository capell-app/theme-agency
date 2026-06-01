<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum CheckoutMode: string implements HasLabel
{
    case Payment = 'payment';
    case Subscription = 'subscription';
    case Setup = 'setup';

    public function getLabel(): string
    {
        return __('capell-payments::generic.checkout_modes.' . $this->value);
    }
}
