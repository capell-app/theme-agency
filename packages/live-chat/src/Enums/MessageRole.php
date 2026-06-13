<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum MessageRole: string implements HasLabel
{
    case Visitor = 'visitor';
    case Assistant = 'assistant';
    case Human = 'human';
    case System = 'system';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.message_role.' . $this->value);
    }
}
