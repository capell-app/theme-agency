<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum AutomationRuleStatus: string implements HasLabel
{
    case Active = 'active';
    case Paused = 'paused';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Active => __('capell-automation-studio::generic.rules.status.active'),
            self::Paused => __('capell-automation-studio::generic.rules.status.paused'),
        };
    }
}
