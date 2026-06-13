<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum EscalationReason: string implements HasLabel
{
    case Manual = 'manual';
    case Keyword = 'keyword';
    case LowConfidence = 'low_confidence';
    case AfterHours = 'after_hours';
    case Sensitive = 'sensitive';
    case RepeatedFailure = 'repeated_failure';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.escalation_reason.' . $this->value);
    }
}
