<?php

declare(strict_types=1);

namespace Capell\Newsletter\Enums;

use Filament\Support\Contracts\HasLabel;

enum SyncStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Running = 'running';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Exhausted = 'exhausted';
    case RetryScheduled = 'retry_scheduled';

    public function getLabel(): string
    {
        return __('capell-newsletter::generic.sync_status.' . $this->value);
    }
}
