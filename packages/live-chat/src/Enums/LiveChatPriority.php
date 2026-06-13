<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum LiveChatPriority: string implements HasLabel
{
    case Normal = 'normal';
    case High = 'high';
    case Urgent = 'urgent';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.priority.' . $this->value);
    }
}
