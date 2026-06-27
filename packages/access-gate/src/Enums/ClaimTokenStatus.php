<?php

declare(strict_types=1);

namespace Capell\AccessGate\Enums;

use Filament\Support\Contracts\HasLabel;

enum ClaimTokenStatus: string implements HasLabel
{
    case Active = 'active';
    case Claimed = 'claimed';
    case Revoked = 'revoked';
    case Expired = 'expired';

    public function getLabel(): string
    {
        return __(sprintf('capell-access-gate::filament.claim_token_status.%s', $this->value));
    }
}
