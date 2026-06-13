<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum LiveChatIntent: string implements HasLabel
{
    case Sales = 'sales';
    case Support = 'support';
    case Complaint = 'complaint';
    case Billing = 'billing';
    case TechnicalIssue = 'technical_issue';
    case General = 'general';
    case Urgent = 'urgent';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.intent.' . $this->value);
    }
}
