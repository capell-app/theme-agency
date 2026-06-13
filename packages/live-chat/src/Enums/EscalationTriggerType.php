<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum EscalationTriggerType: string implements HasLabel
{
    case Keyword = 'keyword';
    case Intent = 'intent';
    case LowConfidence = 'low_confidence';
    case AfterHours = 'after_hours';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.trigger_type.' . $this->value);
    }
}
