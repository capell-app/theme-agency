<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentIntentStatus: string implements HasLabel
{
    case RequiresPaymentMethod = 'requires_payment_method';
    case RequiresConfirmation = 'requires_confirmation';
    case RequiresAction = 'requires_action';
    case Processing = 'processing';
    case RequiresCapture = 'requires_capture';
    case Canceled = 'canceled';
    case Succeeded = 'succeeded';
    case Unknown = 'unknown';

    public function getLabel(): string
    {
        return __('capell-payments::generic.payment_intent_statuses.' . $this->value);
    }
}
