<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum ConversationFlow: string implements HasLabel
{
    case MessageFirst = 'message_first';
    case DetailsFirst = 'details_first';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.conversation_flow.' . $this->value);
    }
}
