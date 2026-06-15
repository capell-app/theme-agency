<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum AutomationRuleConditionOperator: string implements HasLabel
{
    case Equals = 'equals';
    case NotEquals = 'not_equals';
    case Filled = 'filled';
    case Blank = 'blank';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Equals => __('capell-automation-studio::generic.condition_operators.equals'),
            self::NotEquals => __('capell-automation-studio::generic.condition_operators.not_equals'),
            self::Filled => __('capell-automation-studio::generic.condition_operators.filled'),
            self::Blank => __('capell-automation-studio::generic.condition_operators.blank'),
        };
    }
}
