<?php

declare(strict_types=1);

namespace Capell\LiveChat\Enums;

use Filament\Support\Contracts\HasLabel;

enum LiveChatAIRunStatus: string implements HasLabel
{
    case Succeeded = 'succeeded';
    case Refused = 'refused';
    case Failed = 'failed';
    case Fallback = 'fallback';

    public function getLabel(): string
    {
        return __('capell-live-chat::generic.ai_run_status.' . $this->value);
    }
}
