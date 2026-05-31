<?php

declare(strict_types=1);

namespace Capell\Payments\Enums;

use Filament\Support\Contracts\HasLabel;

enum PaymentPurpose: string implements HasLabel
{
    case OneOff = 'one_off';
    case Subscription = 'subscription';
    case Donation = 'donation';
    case PaidDownload = 'paid_download';
    case GatedAccess = 'gated_access';
    case FormPayment = 'form_payment';

    public function getLabel(): string
    {
        return __('capell-payments::generic.purposes.' . $this->value);
    }
}
