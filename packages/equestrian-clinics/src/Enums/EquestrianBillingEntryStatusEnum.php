<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianBillingEntryStatusEnum: string implements HasLabel
{
    case Draft = 'draft';
    case Invoiced = 'invoiced';
    case Paid = 'paid';
    case Void = 'void';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.billing_entry_statuses.' . $this->value);
    }
}
