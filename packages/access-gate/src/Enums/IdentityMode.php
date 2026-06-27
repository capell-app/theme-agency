<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum IdentityMode: string implements HasLabel
{
    case GuestLink = 'guest_link';
    case Authenticated = 'authenticated';
    case Hybrid = 'hybrid';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.identity_mode.%s', $this->value));
    }
}
