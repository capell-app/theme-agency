<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianPaymentStatusEnum: string implements HasLabel
{
    case Pending = 'pending';
    case Authorized = 'authorized';
    case Paid = 'paid';
    case CashApproved = 'cash_approved';
    case Refunded = 'refunded';
    case Failed = 'failed';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.payment_statuses.' . $this->value);
    }
}
