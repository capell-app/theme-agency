<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum GrantStatus: string implements HasLabel
{
    case Active = 'active';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.grant_status.%s', $this->value));
    }
}
