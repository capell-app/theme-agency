<?php

declare(strict_types=1);

namespace Capell\AgentBridge\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum AgentBridgeTokenLifecycleStatus: string implements HasColor, HasLabel
{
    case Active = 'active';
    case Expired = 'expired';
    case Revoked = 'revoked';

    public function getColor(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Expired => 'warning',
            self::Revoked => 'danger',
        };
    }

    public function getLabel(): string
    {
        return (string) __('capell-agent-bridge::admin.token_status_' . $this->value);
    }
}
