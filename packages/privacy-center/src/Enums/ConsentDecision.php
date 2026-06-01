<?php

declare(strict_types=1);

namespace Capell\PrivacyCenter\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConsentDecision: string implements HasLabel
{
    case Granted = 'granted';
    case Denied = 'denied';
    case Withdrawn = 'withdrawn';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __('capell-privacy-center::privacy.consent_decisions.' . $this->value);
    }
}
