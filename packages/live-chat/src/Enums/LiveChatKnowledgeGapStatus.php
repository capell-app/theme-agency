<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum LiveChatKnowledgeGapStatus: string implements HasLabel
{
    case Open = 'open';
    case Reviewed = 'reviewed';
    case Resolved = 'resolved';
    case Ignored = 'ignored';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.knowledge_gap_status.' . $this->value);
    }
}
