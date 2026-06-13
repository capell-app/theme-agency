<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum KnowledgeSourceStatus: string implements HasLabel
{
    case Active = 'active';
    case Paused = 'paused';
    case Syncing = 'syncing';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.knowledge_source_status.' . $this->value);
    }
}
