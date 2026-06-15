<?php

declare(strict_types=1);

namespace Capell\EquestrianClinics\Enums;

use Filament\Support\Contracts\HasLabel;

enum EquestrianPaymentFeeModeEnum: string implements HasLabel
{
    case Universal = 'universal';
    case MethodSpecific = 'method_specific';
    case Absorbed = 'absorbed';

    public function getLabel(): string
    {
        return __('capell-equestrian-clinics::package.payment_fee_modes.' . $this->value);
    }
}
