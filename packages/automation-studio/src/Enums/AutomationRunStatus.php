<?php

declare(strict_types=1);

namespace Capell\AutomationStudio\Enums;

use Filament\Support\Contracts\HasLabel;
use Override;

enum AutomationRunStatus: string implements HasLabel
{
    case Pending = 'pending';
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Skipped = 'skipped';

    #[Override]
    public function getLabel(): string
    {
        return match ($this) {
            self::Pending => __('capell-automation-studio::generic.runs.status.pending'),
            self::Succeeded => __('capell-automation-studio::generic.runs.status.succeeded'),
            self::Failed => __('capell-automation-studio::generic.runs.status.failed'),
            self::Skipped => __('capell-automation-studio::generic.runs.status.skipped'),
        };
    }
}
