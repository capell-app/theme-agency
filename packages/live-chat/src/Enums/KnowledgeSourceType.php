<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum KnowledgeSourceType: string implements HasLabel
{
    case Website = 'website';
    case KnowledgeBase = 'knowledge_base';
    case Policy = 'policy';
    case Manual = 'manual';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.knowledge_source_type.' . $this->value);
    }
}
