<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConversationStatus: string implements HasLabel
{
    case Active = 'active';
    case WaitingForVisitor = 'waiting_for_visitor';
    case WaitingForHuman = 'waiting_for_human';
    case Closed = 'closed';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.conversation_status.' . $this->value);
    }
}
