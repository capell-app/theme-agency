<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum LiveChatSourcePolicy: string implements HasLabel
{
    case Manual = 'manual';
    case ManualAndKnowledgeBase = 'manual_and_knowledge_base';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.source_policy.' . $this->value);
    }
}
