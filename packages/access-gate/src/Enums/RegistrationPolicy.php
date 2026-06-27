<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum RegistrationPolicy: string implements HasLabel
{
    case SinglePerEmail = 'single_per_email';
    case DuplicateAllowed = 'duplicate_allowed';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.registration_policy.%s', $this->value));
    }
}
